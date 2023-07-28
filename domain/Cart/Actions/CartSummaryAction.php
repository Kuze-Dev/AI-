<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Address\Models\Address;
use Domain\Cart\DataTransferObjects\CartSummaryShippingData;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Cart\DataTransferObjects\SummaryData;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Models\Customer;
use Domain\Discount\Actions\DiscountHelperFunctions;
use Domain\Discount\Models\Discount;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Domain\Shipment\Actions\GetBoxAction;
use Domain\Shipment\Actions\GetShippingfeeAction;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShipFromAddressData;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\Taxation\Facades\Taxation;
use Domain\Taxation\Models\TaxZone;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Domain\Shipment\API\Box\DataTransferObjects\BoxData;

class CartSummaryAction
{
    public function getSummary(
        CartLine|Collection $collections,
        CartSummaryTaxData $cartSummaryTaxData,
        CartSummaryShippingData $cartSummaryShippingData,
        ?Discount $discount,
        ?int $serviceId
    ): SummaryData {

        $subtotal = $this->getSubTotal($collections);

        $tax = $this->getTax($cartSummaryTaxData->countryId, $cartSummaryTaxData->stateId);

        $taxTotal = $tax['taxPercentage'] ? round($subtotal * $tax['taxPercentage'] / 100, 2) : 0;

        $shippingTotal = $this->getShippingFee(
            $collections,
            $cartSummaryShippingData->customer,
            $cartSummaryShippingData->shippingAddress,
            $cartSummaryShippingData->shippingMethod,
            $serviceId
        );

        $discountTotal = $this->getDiscount($discount, $subtotal, $shippingTotal);

        $grandTotal = $subtotal + $taxTotal + $shippingTotal;

        $discountMessages = (new DiscountHelperFunctions())->validateDiscountCode($discount, $grandTotal);

        $summaryData = [
            'subTotal' => $subtotal,
            'taxZone' => $tax['taxZone'],
            'taxDisplay' => $tax['taxDisplay'],
            'taxPercentage' => $tax['taxPercentage'],
            'taxTotal' => $taxTotal,
            'grandTotal' => $grandTotal - ($discountMessages->status == 'valid' ? $discountTotal : 0),
            'discountTotal' => $discountMessages->status == 'valid' ? $discountTotal : 0,
            'discountMessages' => $discountMessages,
            'shippingTotal' => $shippingTotal,
        ];

        return SummaryData::fromArray($summaryData);
    }

    public function getSubTotal(CartLine|Collection $collections): float
    {
        $subTotal = 0;

        if ($collections instanceof Collection) {
            $subTotal = $collections->reduce(function ($carry, $collection) {
                $purchasable = $collection->purchasable;

                return $carry + ($purchasable->selling_price * $collection->quantity);
            }, 0);
        } elseif ($collections instanceof CartLine) {
            $subTotal = $collections->purchasable->selling_price * $collections->quantity;
        }

        return $subTotal;
    }

    public function getShippingFee(
        CartLine|Collection $collections,
        Customer $customer,
        ?Address $shippingAddress,
        ?ShippingMethod $shippingMethod,
        ?int $serviceId
    ): float {
        $shippingFeeTotal = 0;

        $productlist = $this->getProducts($collections);

        $boxData = app(GetBoxAction::class)->execute(
            $shippingMethod,
            BoxData::fromArray($productlist)
        );

        if ($shippingAddress) {
            $parcelData = new ParcelData(
                ship_from_address: new ShipFromAddressData(
                    address: $shippingMethod->shipper_address,
                    city: $shippingMethod->shipper_city,
                    state: $shippingMethod->state,
                    zipcode: $shippingMethod->shipper_zipcode,
                    country: $shippingMethod->country,
                    code: $shippingMethod->country->code,
                ),
                pounds: (string) $boxData->weight,
                ounces: '0',
                width: (string) $boxData->width,
                height: (string) $boxData->height,
                length: (string) $boxData->length,
                zip_origin: $shippingMethod->shipper_zipcode,
                parcel_value: '200',
            );

            $shippingFeeTotal = app(GetShippingfeeAction::class)
                ->execute($customer, $parcelData, $shippingMethod, $shippingAddress, $serviceId);
        }

        return $shippingFeeTotal;
    }

    private function getProducts(CartLine|Collection $collections): array
    {
        $productlist = [];

        if ($collections instanceof Collection) {
            foreach ($collections as $collection) {
                if ($collection->purchasable instanceof Product) {
                    $product = $collection->purchasable;

                    $purchasableId = $product->id;
                    $length = $product->dimension['length'];
                    $width = $product->dimension['width'];
                    $height = $product->dimension['height'];
                    $weight = $product->weight;
                } else if ($collection->purchasable instanceof ProductVariant) {
                    $product = $collection->purchasable->product;

                    $purchasableId = $collection->purchasable->id;
                    $length = $product->dimension['length'];
                    $width = $product->dimension['width'];
                    $height = $product->dimension['height'];
                    $weight = $product->weight;
                }

                $productlist[] = [
                    'product_id' => (string) $purchasableId,
                    'length' => $length,
                    'width' => $width,
                    'height' => $height,
                    'weight' => (float) $weight,
                ];
            }
        } else {
            $cartLine = $collections->purchasable;

            if ($cartLine instanceof Product) {
                $product = $cartLine;

                $purchasableId = $product->id;
                $length = $product->dimension['length'];
                $width = $product->dimension['width'];
                $height = $product->dimension['height'];
                $weight = $product->weight;
            } else if ($cartLine instanceof ProductVariant) {
                $product = $cartLine->product;

                $purchasableId = $cartLine->id;
                $length = $product->dimension['length'];
                $width = $product->dimension['width'];
                $height = $product->dimension['height'];
                $weight = $product->weight;
            }

            $productlist[] = [
                'product_id' => (string) $purchasableId,
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'weight' => (float) $weight,
            ];
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

        if (!$taxZone instanceof TaxZone) {
            throw new BadRequestHttpException('No tax zone found');
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

        if (!is_null($discount)) {
            $discountTotal = (new DiscountHelperFunctions())->deductableAmount($discount, $subTotal, $shippingTotal);
        }

        return $discountTotal;
    }
}
