<?php

declare(strict_types=1);

namespace Domain\Cart\Helpers;

use Domain\Address\Models\Address;
use Domain\Cart\DataTransferObjects\SummaryData;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Models\Customer;
use Domain\Discount\Actions\DiscountHelperFunctions;
use Domain\Discount\Models\Discount;
use Domain\Shipment\API\USPS\Exceptions\USPSServiceNotFoundException;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\Taxation\Facades\Taxation;
use Domain\Taxation\Models\TaxZone;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CartLineHelper
{
    public function summary(
        Collection $collections,
        int $countryId,
        ?int $stateId = null,
        ?Discount $discount = null
    ) {
        $taxZone = Taxation::getTaxZone($countryId, $stateId);
        $taxPercentage = (float) $taxZone->percentage;
        $taxDisplay = $taxZone->price_display;

        if (!$taxZone instanceof TaxZone) {
            throw new BadRequestHttpException('No tax zone found');
        }

        $summary = $this->calculate($collections, $taxPercentage, $discount);

        $summaryData = [
            'subTotal' => $summary['subTotal'],
            'taxZone' => $taxZone,
            'taxDisplay' => $taxDisplay,
            'taxPercentage' => $taxPercentage,
            'taxTotal' => $summary['taxTotal'],
            'grandTotal' => $summary['grandTotal'],
            'discountTotal' => $summary['discountTotal'],
            'discountMessage' => $summary['discountTotal'] == 0 ? 'Invalid discount' : 'Discount is valid',
        ];

        return SummaryData::fromArray($summaryData);
    }

    public function calculate(CartLine|Collection $collections, float $taxPercentage, ?Discount $discount = null)
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

        $taxTotal = round($subTotal * $taxPercentage / 100, 2);

        $discountTotal = 0;
        if (!is_null($discount)) {
            \Log::info($discount);
            $discountTotal = (new DiscountHelperFunctions())->deductOrderSubtotal($discount, $subTotal);
        }

        \Log::info($discountTotal);

        //for now, but the shipping fee and discount will be added
        $grandTotal = $subTotal + $taxTotal - $discountTotal;

        return [
            'subTotal' => round($subTotal, 2),
            'taxTotal' => round($taxTotal, 2),
            'discountTotal' => round($discountTotal, 2),
            'grandTotal' => round($grandTotal, 2),
        ];
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
        Customer $customer,
        Address $shippingAddress,
        ShippingMethod $shippingMethod,
    ): float {
        $shippingFeeTotal = 0;
        try {
            if ($shippingAddress) {
                $parcelData =  new ParcelData(
                    pounds: '10',
                    ounces: '0',
                    width: '10',
                    height: '10',
                    length: '10',
                    zip_origin: $shippingMethod->ship_from_address['zip5'],
                    parcel_value: '200',
                );

                $shippingFeeTotal = app(GetUSPSRateAction::class)
                    ->execute($customer, $parcelData, $shippingMethod, $shippingAddress);
            }
        } catch (USPSServiceNotFoundException) {
            return response()->json([
                "service_id" => "Service id is required",
            ], 404);
        }

        return $shippingFeeTotal;
    }

    public function getTax(
        int $countryId,
        ?int $stateId = null
    ) {
        $taxZone = Taxation::getTaxZone($countryId, $stateId);
        $taxPercentage = (float) $taxZone->percentage;
        $taxDisplay = $taxZone->price_display;

        if (!$taxZone instanceof TaxZone) {
            throw new BadRequestHttpException('No tax zone found');
        }

        return [
            'taxZone' => $taxZone,
            'taxDisplay' => $taxDisplay,
            'taxPercentage' => $taxPercentage,
        ];
    }

    public function getDiscount(?Discount $discount, float $subTotal)
    {
        return (new DiscountHelperFunctions())->deductOrderSubtotal($discount, $subTotal) ?? 0;
    }
}
