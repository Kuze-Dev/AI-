<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Address\Models\Address;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderTaxData;
use Domain\ServiceOrder\Models\ServiceOrder;
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

        return $this->computeTotalPriceWithTax($subTotal, $taxZone);
    }

    public function computeTotalPriceWithTax(float $subTotal, TaxZone|ServiceOrder|null $model): ServiceOrderTaxData
    {
        /** @var PriceDisplay|null $taxDisplay */
        $taxDisplay = null;

        /** @var int|float $taxPercentage */
        $taxPercentage = 0;

        /** @var int|float $taxTotal */
        $taxTotal = 0;

        /** @var float $totalPrice */
        $totalPrice = $subTotal;

        if ($model instanceof TaxZone) {
            $taxPercentage = (float) $model->percentage;
            $taxDisplay = $model->price_display;
        }

        if ($model instanceof ServiceOrder) {
            $taxPercentage = (float) $model->tax_percentage;
            $taxDisplay = $model->tax_display;
        }

        if ($taxDisplay === PriceDisplay::EXCLUSIVE) {
            $taxTotal = $subTotal * ($taxPercentage / 100.0);

            $totalPrice = $subTotal + $taxTotal;
        }

        return new ServiceOrderTaxData(
            sub_total: $subTotal,
            tax_display: $taxDisplay,
            tax_percentage: $taxPercentage,
            tax_total: $taxTotal,
            total_price: $totalPrice
        );
    }
}
