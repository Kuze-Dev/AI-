<?php

declare(strict_types=1);

namespace Domain\Cart\Helpers;

use Domain\Address\Models\Address;
use Domain\Cart\DataTransferObjects\CartSummaryShippingData;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Cart\DataTransferObjects\SummaryData;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Models\Customer;
use Domain\Discount\Actions\DiscountHelperFunctions;
use Domain\Discount\Models\Discount;
use Domain\Shipment\Actions\USPS\GetUSPSRateAction;
use Domain\Shipment\API\USPS\Exceptions\USPSServiceNotFoundException;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\Taxation\Facades\Taxation;
use Domain\Taxation\Models\TaxZone;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CartLineHelper
{
    public function getSummary(
        CartLine|Collection $collections,
        CartSummaryTaxData $cartSummaryTaxData,
        CartSummaryShippingData $cartSummaryShippingData,
        ?Discount $discount
    ) {

        $subtotal = $this->getSubTotal($collections);

        $tax = $this->getTax($cartSummaryTaxData->countryId, $cartSummaryTaxData->stateId);
        $taxTotal = round($subtotal * $tax['taxPercentage'] / 100, 2);

        $discountTotal = $this->getDiscount($discount, $subtotal);

        $shippingTotal = $this->getShippingFee(
            $cartSummaryShippingData->customer,
            $cartSummaryShippingData->shippingAddress,
            $cartSummaryShippingData->shippingMethod
        );

        $grandTotal = $subtotal + $taxTotal + $shippingTotal - $discountTotal;

        $summaryData = [
            'subTotal' => $subtotal,
            'taxZone' => $tax['taxZone'],
            'taxDisplay' => $tax['taxDisplay'],
            'taxPercentage' => $tax['taxPercentage'],
            'taxTotal' => $taxTotal,
            'grandTotal' => $grandTotal,
            'discountTotal' => $discountTotal,
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
        Customer $customer,
        ?Address $shippingAddress,
        ?ShippingMethod $shippingMethod,
    ): float {
        $shippingFeeTotal = 0;

        try {
            if ($shippingAddress) {
                $parcelData = new ParcelData(
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
                'service_id' => 'Service id is required',
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
        $discountTotal = 0;

        if (!is_null($discount)) {
            $discountTotal = (new DiscountHelperFunctions())->deductOrderSubtotal($discount, $subTotal);
        }

        return $discountTotal;
    }
}
