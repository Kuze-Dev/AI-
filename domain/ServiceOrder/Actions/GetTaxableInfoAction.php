<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Address\Models\Address;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderTaxData;
use Domain\Taxation\Enums\PriceDisplay;
use Domain\Taxation\Facades\Taxation;
use Domain\Taxation\Models\TaxZone;
use Throwable;

class GetTaxableInfoAction
{
    /** @throws Throwable */
    public function execute(
        float $subTotal,
        Address $billingAddressData
    ): ServiceOrderTaxData {

        /** @var TaxZone $taxZone */
        $taxZone = Taxation::getTaxZone(
            $billingAddressData->state->country_id,
            $billingAddressData->state_id
        );

        /** @var PriceDisplay|null $taxDisplay */
        $taxDisplay = null;

        /** @var int|float $taxPercentage */
        $taxPercentage = 0;

        /** @var int|float $taxTotal */
        $taxTotal = 0;

        /** @var float $totalPrice */
        $totalPrice = $subTotal;

        if ($taxZone instanceof TaxZone) {
            $taxPercentage = (float) $taxZone->percentage;

            $taxDisplay = $taxZone->price_display;

            if ($taxZone->price_display === PriceDisplay::EXCLUSIVE) {
                $taxTotal = $this->computeExclusiveTax($subTotal, $taxPercentage);

                $totalPrice = $subTotal + $taxTotal;
            }
        }

        return new ServiceOrderTaxData(
            sub_total: $subTotal,
            tax_display: $taxDisplay,
            tax_percentage: $taxPercentage,
            tax_total: $taxTotal,
            total_price: $totalPrice
        );

    }

    public function computeExclusiveTax(
        float $subTotal,
        int|float $taxPercentage
    ): float {
        return $subTotal * ($taxPercentage / 100.0);
    }
}
