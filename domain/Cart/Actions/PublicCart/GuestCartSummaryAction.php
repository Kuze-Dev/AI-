<?php

declare(strict_types=1);

namespace Domain\Cart\Actions\PublicCart;

use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Cart\DataTransferObjects\GuestCartSummaryShippingData;
use Domain\Cart\DataTransferObjects\SummaryData;
use Domain\Cart\Models\CartLine;
use Domain\Discount\Actions\DiscountHelperFunctions;
use Domain\Discount\Enums\DiscountConditionType;
use Domain\Discount\Models\Discount;
use Domain\Product\Models\ProductVariant;
use Domain\Shipment\Actions\GetBoxAction;
use Domain\Shipment\Actions\GetShippingfeeAction;
use Domain\Shipment\API\Box\DataTransferObjects\BoxData;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ReceiverData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\Shipment\Enums\UnitEnum;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Facades\Taxation;
use Domain\Taxation\Models\TaxZone;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class GuestCartSummaryAction
{
    /** @param  \Domain\Cart\Models\CartLine|\Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine>  $collections */
    public function execute(
        CartLine|Collection $collections,
        CartSummaryTaxData $cartSummaryTaxData,
        GuestCartSummaryShippingData $cartSummaryShippingData,
        ?Discount $discount,
        int|string|null $serviceId
    ): SummaryData {

        $initialSubTotal = $this->getSubTotal($collections);

        $tax = $this->getTax($cartSummaryTaxData->countryId, $cartSummaryTaxData->stateId);

        $taxTotal = $tax['taxPercentage'] ? round($initialSubTotal * $tax['taxPercentage'] / 100, 2) : 0;

        if ($tax['taxDisplay'] == PriceDisplay::INCLUSIVE) {
            $taxTotal = 0;
        }

        $initialShippingTotal = $this->getShippingFee(
            $collections,
            $cartSummaryShippingData->receiverData,
            $cartSummaryShippingData->shippingAddress,
            $cartSummaryShippingData->shippingMethod,
            $serviceId
        );

        $discountTotal = $this->getDiscount($discount, $initialSubTotal, $initialShippingTotal);

        $initialTotal = $initialSubTotal + $taxTotal + $initialShippingTotal;

        $subtotal = $initialSubTotal;
        $shippingTotal = $initialShippingTotal;

        $discountMessages = (new DiscountHelperFunctions())->validateDiscountCode($discount, $initialTotal);

        if ($discountMessages->status == 'valid') {
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

    /** @param  \Domain\Cart\Models\CartLine|\Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine>  $collections */
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

    /** @param  \Domain\Cart\Models\CartLine|\Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine>  $collections */
    public function getShippingFee(
        CartLine|Collection $collections,
        ?ReceiverData $receiverData,
        ?ShippingAddressData $shippingAddress,
        ?ShippingMethod $shippingMethod,
        int|string|null $serviceId
    ): float {
        $shippingFeeTotal = 0;

        if ($shippingAddress && $shippingMethod && $receiverData) {
            $productlist = $this->getProducts($collections, UnitEnum::INCH);

            $subTotal = $this->getSubTotal($collections);

            $boxResponse = app(GetBoxAction::class)->execute(
                $shippingMethod,
                $shippingAddress,
                BoxData::fromArray($productlist)
            );

            $parcelData = new ParcelData(
                reciever: $receiverData,
                pounds: (string) $boxResponse->weight,
                ounces: '0',
                zip_origin: $shippingMethod->shipper_zipcode,
                parcel_value: (string) $subTotal,
                height: (string) $boxResponse->height,
                width: (string) $boxResponse->width,
                length: (string) $boxResponse->length,
                boxData: $boxResponse->boxData,
                ship_from_address: new ShippingAddressData(
                    address: $shippingMethod->shipper_address,
                    city: $shippingMethod->shipper_city,
                    state: $shippingMethod->state,
                    zipcode: $shippingMethod->shipper_zipcode,
                    country: $shippingMethod->country,
                    code: $shippingMethod->country->code,
                ),
            );

            $shippingFeeTotal = app(GetShippingfeeAction::class)
                ->execute($parcelData, $shippingMethod, $shippingAddress, $serviceId);
        }

        return $shippingFeeTotal;
    }

    /** @param  \Domain\Cart\Models\CartLine|\Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine>  $collections */
    public function getProducts(CartLine|Collection $collections, ?UnitEnum $unit = UnitEnum::CM): array
    {
        $productlist = [];

        $measurement = match ($unit) {
            UnitEnum::INCH => 1 / 2.54,
            default => 1,
        };

        if (! is_iterable($collections)) {
            /** @var \Domain\Product\Models\Product $product */
            $product = $collections->purchasable;

            if ($collections->purchasable instanceof ProductVariant) {
                /** @var \Domain\Product\Models\Product $product */
                $product = $collections->purchasable->product;
            }

            if (! is_null($product->dimension)) {
                $purchasableId = $product->id;

                $length = $product->dimension['length'];
                $width = $product->dimension['width'];
                $height = $product->dimension['height'];
                $weight = $product->weight;

                for ($i = 0; $i < $collections->quantity; $i++) {
                    $productlist[] = [
                        'product_id' => (string) $purchasableId,
                        'length' => ceil($length * $measurement),
                        'width' => ceil($width * $measurement),
                        'height' => ceil($height * $measurement),
                        'weight' => (float) $weight,
                    ];
                }
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
                if (! is_null($product->dimension)) {
                    $purchasableId = $product->id;

                    $length = $product->dimension['length'];
                    $width = $product->dimension['width'];
                    $height = $product->dimension['height'];
                    $weight = $product->weight;

                    for ($i = 0; $i < $collection->quantity; $i++) {
                        $productlist[] = [
                            'product_id' => (string) $purchasableId,
                            'length' => ceil($length * $measurement),
                            'width' => ceil($width * $measurement),
                            'height' => ceil($height * $measurement),
                            'weight' => (float) $weight,
                        ];
                    }
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

        if (! $taxZone instanceof TaxZone) {
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

        if (! is_null($discount)) {
            $discountTotal = (new DiscountHelperFunctions())->deductableAmount($discount, $subTotal, $shippingTotal) ?? 0;
        }

        return $discountTotal;
    }
}
