<?php

declare(strict_types=1);

namespace Domain\Cart\Helpers;

use Domain\Cart\DataTransferObjects\SummaryData;
use Domain\Taxation\Facades\Taxation;
use Illuminate\Database\Eloquent\Collection;

class CartLineHelper
{
    public function calculate(Collection $cartLines, int $countryId, ?int $stateId = null)
    {
        $subTotal = $cartLines->reduce(function ($carry, $cartLine) {
            $purchasable = $cartLine->purchasable;

            return $carry + ($purchasable->selling_price * $cartLine->quantity);
        }, 0);

        $taxZone = Taxation::getTaxZone($countryId, $stateId);
        $taxDisplay = $taxZone->price_display;
        $taxPercentage = (float) $taxZone->percentage;
        $taxTotal = round($subTotal * $taxPercentage / 100, 2);

        //for now, but the shipping fee and discount will be added
        $grandTotal = $subTotal + $taxTotal;

        $summaryData = [
            'subTotal' => $subTotal,
            'taxZone' => $taxZone,
            'taxDisplay' => $taxDisplay,
            'taxPercentage' => $taxPercentage,
            'taxTotal' => $taxTotal,
            'grandTotal' => $grandTotal,
        ];

        return SummaryData::fromArray($summaryData);
    }
}
