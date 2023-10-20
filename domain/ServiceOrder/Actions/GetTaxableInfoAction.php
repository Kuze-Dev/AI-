<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Address\Models\Address;
use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Facades\Taxation;
use Domain\Taxation\Models\TaxZone;
use Throwable;

class GetTaxableInfoAction
{
    /** @throws Throwable */
    public function execute(float $subTotal, Address $billingAddressData): array
    {

        $countryId = $billingAddressData->state->country_id;
        $stateId = $billingAddressData->state_id;

        $taxZone = Taxation::getTaxZone($countryId, $stateId);

        if ( ! $taxZone instanceof TaxZone) {
            $taxDisplay = null;
            $taxPercentage = 0;
            $taxTotal = 0;
            $totalPrice = $subTotal;
        } else {
            $taxPercentage = $taxZone->percentage;
            $taxDisplay = $taxZone->price_display;

            if ($taxZone->price_display === PriceDisplay::EXCLUSIVE) {
                $taxTotal = $subTotal * ($taxPercentage / 100.0);
                $totalPrice = $subTotal + $taxTotal;
            } else {
                $taxTotal = 0;
                $totalPrice = $subTotal;
            }
        }

        return [
            'subTotal' => $subTotal,
            'taxDisplay' => $taxDisplay,
            'taxPercentage' => $taxPercentage,
            'taxTotal' => $taxTotal,
            'totalPrice' => $totalPrice,
        ];

    }
}
