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
use Domain\Shipment\Actions\USPS\GetUSPSRateAction;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\Taxation\Facades\Taxation;
use Domain\Taxation\Models\TaxZone;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
                ->execute($customer, $parcelData, $shippingMethod, $shippingAddress, $serviceId);
        }

        return $shippingFeeTotal;
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
        $taxPercentage = (float) $taxZone->percentage;
        $taxDisplay = $taxZone->price_display;

        if ( ! $taxZone instanceof TaxZone) {
            throw new BadRequestHttpException('No tax zone found');
        }

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
            $discountTotal = (new DiscountHelperFunctions())->deductableAmount($discount, $subTotal, $shippingTotal);
        }

        return $discountTotal;
    }
}
