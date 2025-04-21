<?php

declare(strict_types=1);

namespace Domain\Taxation\Actions;

use Domain\Taxation\DataTransferObjects\TaxZoneData;
use Domain\Taxation\Enums\TaxZoneType;
use Domain\Taxation\Models\TaxZone;
use InvalidArgumentException;

class UpdateTaxZoneAction
{
    public function execute(TaxZone $taxZone, TaxZoneData $taxZoneData): TaxZone
    {
        if ($taxZoneData->is_default) {
            TaxZone::whereIsDefault(true)
                ->update(['is_default' => false]);
        }

        $taxZone->update([
            'name' => $taxZoneData->name,
            'price_display' => $taxZoneData->price_display,
            'is_active' => $taxZoneData->is_active,
            'is_default' => $taxZoneData->is_default,
            'type' => $taxZoneData->type,
            'percentage' => $taxZoneData->percentage,
        ]);

        $this->syncTaxZoneByType($taxZone, $taxZoneData);

        return $taxZone;
    }

    protected function syncTaxZoneByType(TaxZone $taxZone, TaxZoneData $taxZoneData): void
    {
        match ($taxZoneData->type) {
            TaxZoneType::COUNTRY => $this->saveCountryTaxZone($taxZone, $taxZoneData),
            TaxZoneType::STATE => $this->saveStateTaxZone($taxZone, $taxZoneData),
        };
    }

    protected function saveCountryTaxZone(TaxZone $taxZone, TaxZoneData $taxZoneData): void
    {
        $taxZone->countries()->sync($taxZoneData->countries);
        $taxZone->states()->sync([]);
    }

    protected function saveStateTaxZone(TaxZone $taxZone, TaxZoneData $taxZoneData): void
    {
        if (count($taxZoneData->countries) > 1) {
            throw new InvalidArgumentException;
        }

        $taxZone->countries()->sync($taxZoneData->countries);
        $taxZone->states()->sync($taxZoneData->states);
    }
}
