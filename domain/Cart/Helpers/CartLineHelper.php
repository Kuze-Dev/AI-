<?php

declare(strict_types=1);

namespace Domain\Cart\Helpers;

use Domain\Cart\DataTransferObjects\SummaryData;
use Domain\Cart\Models\CartLine;
use Domain\Discount\Actions\DiscountHelperFunctions;
use Domain\Discount\Models\Discount;
use Domain\Taxation\Facades\Taxation;
use Domain\Taxation\Models\TaxZone;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CartLineHelper
{
    public function summary(Collection $collections, int $countryId, ?int $stateId = null)
    {
        $taxZone = Taxation::getTaxZone($countryId, $stateId);
        $taxPercentage = (float) $taxZone->percentage;
        $taxDisplay = $taxZone->price_display;

        if (!$taxZone instanceof TaxZone) {
            throw new BadRequestHttpException('No tax zone found');
        }

        $summary = $this->calculate($collections, $taxPercentage);

        $summaryData = [
            'subTotal' => $summary['subTotal'],
            'taxZone' => $taxZone,
            'taxDisplay' => $taxDisplay,
            'taxPercentage' => $taxPercentage,
            'taxTotal' => $summary['taxTotal'],
            'grandTotal' => $summary['grandTotal'],
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
        } else if ($collections instanceof CartLine) {
            $subTotal = $collections->purchasable->selling_price * $collections->quantity;
        }

        $taxTotal = round($subTotal * $taxPercentage / 100, 2);

        $discountTotal = 0;
        if (!is_null($discount)) {
            $discountTotal = (new DiscountHelperFunctions())->deductOrderSubtotalByFixedValue($discount->code, $subTotal)
                ?: (new DiscountHelperFunctions())->deductOrderSubtotalByPercentageValue($discount->code, $subTotal);
        }

        //for now, but the shipping fee and discount will be added
        $grandTotal = $subTotal + $taxTotal - $discountTotal;

        return [
            'subTotal' => $subTotal,
            'taxTotal' => $taxTotal,
            'discountTotal' => $discountTotal,
            'grandTotal' => $grandTotal
        ];
    }
}
