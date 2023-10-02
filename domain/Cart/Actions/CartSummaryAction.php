<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CartSummaryShippingData;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Cart\DataTransferObjects\SummaryData;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Models\Customer;
use Domain\Discount\Actions\DiscountHelperFunctions;
use Domain\Discount\Enums\DiscountConditionType;
use Domain\Discount\Models\Discount;
use Domain\Product\Models\ProductVariant;
use Domain\Shipment\Actions\GetBoxAction;
use Domain\Shipment\Actions\GetShippingfeeAction;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\Taxation\Facades\Taxation;
use Domain\Taxation\Models\TaxZone;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Domain\Shipment\API\Box\DataTransferObjects\BoxData;
use Domain\Shipment\DataTransferObjects\ReceiverData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;

class CartSummaryAction
{
    /** @param \Domain\Cart\Models\CartLine|\Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> $collections */
    public function getSummary(
        CartLine|Collection $collections,
        CartSummaryTaxData $cartSummaryTaxData,
        CartSummaryShippingData $cartSummaryShippingData,
        ?Discount $discount,
        ?int $serviceId
    ): SummaryData {

        $initialSubTotal = $this->getSubTotal($collections);

        $tax = $this->getTax($cartSummaryTaxData->countryId, $cartSummaryTaxData->stateId);

        $taxTotal = $tax['taxPercentage'] ? round($initialSubTotal * $tax['taxPercentage'] / 100, 2) : 0;

        $initialShippingTotal = 0;

        if ($cartSummaryShippingData->shippingAddress) {
            $shippingAddress = ShippingAddressData::fromAddressModel($cartSummaryShippingData->shippingAddress);

            $initialShippingTotal = $this->getShippingFee(
                $collections,
                $cartSummaryShippingData->customer,
                $shippingAddress,
                $cartSummaryShippingData->shippingMethod,
                $serviceId
            );
        }

        $discountTotal = $this->getDiscount($discount, $initialSubTotal, $initialShippingTotal);

        $initialTotal = $initialSubTotal + $taxTotal + $initialShippingTotal;

        $subtotal = $initialSubTotal;
        $shippingTotal = $initialShippingTotal;

        $discountMessages = (new DiscountHelperFunctions())->validateDiscountCode($discount, $initialTotal);

        if ($discount?->discountCondition?->discount_type === DiscountConditionType::ORDER_SUB_TOTAL) {
            if ($discountTotal >= $initialSubTotal) {
                $subtotal = 0;
            } else {
                $subtotal = $initialSubTotal - $discountTotal;
            }
        }

        if ($discount?->discountCondition?->discount_type === DiscountConditionType::DELIVERY_FEE) {
            if ($discountTotal >= $initialShippingTotal) {
                $shippingTotal = 0;
            } else {
                $shippingTotal = $initialShippingTotal - $discountTotal;
            }
        }

        $grandTotal = $subtotal + $taxTotal + $shippingTotal;

        $summaryData = [
            'initialSubTotal' => $initialSubTotal,
            'subTotal' => $subtotal,
            'taxZone' => $tax['taxZone'],
            'taxDisplay' => $tax['taxDisplay'],
            'taxPercentage' => $tax['taxPercentage'],
            'taxTotal' => $taxTotal,
            'grandTotal' => $grandTotal,
            'discountTotal' => $discountMessages->status == 'valid' ? $discountTotal : 0,
            'discountMessages' => $discountMessages,
            'initialShippingTotal' => $initialShippingTotal,
            'shippingTotal' => $shippingTotal,
        ];

        return SummaryData::fromArray($summaryData);
    }

    /** @param \Domain\Cart\Models\CartLine|\Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> $collections */
    public function getSubTotal(CartLine|Collection $collections): float
    {
        $subTotal = 0;

        if ($collections instanceof Collection) {
            $subTotal = $collections->reduce(function ($carry, $collection) {
                /** @var \Domain\Product\Models\Product|\Domain\Product\Models\ProductVariant $purchasable */
                $purchasable = $collection->purchasable;
                $sellingPrice = (float) $purchasable->selling_price;

                return $carry + ($sellingPrice * $collection->quantity);
            }, 0);
        } elseif ($collections instanceof CartLine) {
            /** @var \Domain\Product\Models\Product|\Domain\Product\Models\ProductVariant $purchasable */
            $purchasable = $collections->purchasable;
            $sellingPrice = (float) $purchasable->selling_price;

            $subTotal = $sellingPrice * $collections->quantity;
        }

        return $subTotal;
    }

    /** @param \Domain\Cart\Models\CartLine|\Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> $collections */
    public function getShippingFee(
        CartLine|Collection $collections,
        Customer $customer,
        ?ShippingAddressData $shippingAddress,
        ?ShippingMethod $shippingMethod,
        ?int $serviceId
    ): float {
        $shippingFeeTotal = 0;

        if ($shippingAddress && $shippingMethod) {
            $productlist = $this->getProducts($collections);

            $subTotal = $this->getSubTotal($collections);

            $boxResponse = app(GetBoxAction::class)->execute(
                $shippingMethod,
                $shippingAddress,
                BoxData::fromArray($productlist)
            );

            /** @var \Domain\Address\Models\State $state */
            $state = $shippingMethod->state;

            /** @var \Domain\Address\Models\Country $country */
            $country = $shippingMethod->country;

            $parcelData = new ParcelData(
                reciever: ReceiverData::fromCustomerModel($customer->load('verifiedAddress')),
                ship_from_address: new ShippingAddressData(
                    address: $shippingMethod->shipper_address,
                    city: $shippingMethod->shipper_city,
                    state: $state,
                    zipcode: $shippingMethod->shipper_zipcode,
                    country: $country,
                    code: $country->code,
                ),
                pounds: (string) $boxResponse->weight,
                ounces: '0',
                width: (string) $boxResponse->width,
                height: (string) $boxResponse->height,
                length: (string) $boxResponse->length,
                zip_origin: $shippingMethod->shipper_zipcode,
                boxData: $boxResponse->boxData,
                parcel_value: (string) $subTotal,
            );

            $shippingFeeTotal = app(GetShippingfeeAction::class)
                ->execute($parcelData, $shippingMethod, $shippingAddress, $serviceId);
        }

        return $shippingFeeTotal;
    }

    /** @param \Domain\Cart\Models\CartLine|\Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> $collections */
    public function getProducts(CartLine|Collection $collections): array
    {
        $productlist = [];

        $cm_to_inches = 1 / 2.54;

        if ( ! is_iterable($collections)) {
            /** @var \Domain\Product\Models\Product $product */
            $product = $collections->purchasable;

            if ($collections->purchasable instanceof ProductVariant) {
                /** @var \Domain\Product\Models\Product $product */
                $product = $collections->purchasable->product;
            }

            if ( ! is_null($product->dimension)) {
                $purchasableId = $product->id;

                $length = $product->dimension['length'];
                $width = $product->dimension['width'];
                $height = $product->dimension['height'];
                $weight = $product->weight;

                $productlist[] = [
                    'product_id' => (string) $purchasableId,
                    'length' => ceil($length * $cm_to_inches * $collections->quantity),
                    'width' => ceil($width * $cm_to_inches * $collections->quantity),
                    'height' => ceil($height * $cm_to_inches * $collections->quantity),
                    'weight' => (float) $weight * $collections->quantity,
                ];
            }
        } else {
            foreach ($collections as $collection) {
                /** @var \Domain\Product\Models\Product $product */
                $product = $collection->purchasable;

                if ($collection->purchasable instanceof ProductVariant) {
                    $collection->purchasable->load('product');

                    /** @var \Domain\Product\Models\Product $product */
                    $product = $collection->purchasable->product;
                }
                if ( ! is_null($product->dimension)) {
                    $purchasableId = $product->id;

                    $length = $product->dimension['length'];
                    $width = $product->dimension['width'];
                    $height = $product->dimension['height'];
                    $weight = $product->weight;

                    $productlist[] = [
                        'product_id' => (string) $purchasableId,
                        'length' => ceil($length * $cm_to_inches * $collection->quantity),
                        'width' => ceil($width * $cm_to_inches * $collection->quantity),
                        'height' => ceil($height * $cm_to_inches * $collection->quantity),
                        'weight' => (float) $weight * $collection->quantity,
                    ];
                }
            }
        }

        return $productlist;
    }

    public function getTax(
        ?int $countryId,
        ?int $stateId = null
    ): array {
        if (is_null($countryId)) {
            return [
                'taxZone' => null,
                'taxDisplay' => null,
                'taxPercentage' => null,
            ];
        }

        $taxZone = Taxation::getTaxZone($countryId, $stateId);

        if ( ! $taxZone instanceof TaxZone) {
            return [
                'taxZone' => null,
                'taxDisplay' => null,
                'taxPercentage' => null,
            ];
            // throw new BadRequestHttpException('No tax zone found');
        }

        $taxPercentage = (float) $taxZone->percentage;
        $taxDisplay = $taxZone->price_display;

        return [
            'taxZone' => $taxZone,
            'taxDisplay' => $taxDisplay,
            'taxPercentage' => $taxPercentage,
        ];
    }

    public function getDiscount(?Discount $discount, float $subTotal, float $shippingTotal): float
    {
        $discountTotal = 0;

        if ( ! is_null($discount)) {
            $discountTotal = (new DiscountHelperFunctions())->deductableAmount($discount, $subTotal, $shippingTotal) ?? 0;
        }

        return $discountTotal;
    }
}
