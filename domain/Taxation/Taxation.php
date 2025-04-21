<?php

declare(strict_types=1);

namespace Domain\Taxation;

use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Taxation\Enums\TaxZoneType;
use Domain\Taxation\Models\TaxZone;

class Taxation
{
    public function __construct() {}

    public function getTaxZone(Country|int $country, State|int|null $state = null): ?TaxZone
    {
        if ($state && $taxZone = $this->getTaxZoneByState($country, $state)) {
            return $taxZone;
        }

        if ($taxZone = $this->getTaxZoneByCountry($country)) {
            return $taxZone;
        }

        return $this->getDefaultTaxZone();
    }

    protected function getTaxZoneByState(Country|int $country, State|int $state): ?TaxZone
    {
        return TaxZone::query()
            ->whereIsActive(true)
            ->whereType(TaxZoneType::STATE)
            ->whereHas('states', fn ($query) => $query->whereKey($state))
            ->whereHas('countries', fn ($query) => $query->whereKey($country))
            ->first();
    }

    protected function getTaxZoneByCountry(Country|int $country): ?TaxZone
    {
        return TaxZone::query()
            ->whereIsActive(true)
            ->whereType(TaxZoneType::COUNTRY)
            ->whereHas('countries', fn ($query) => $query->whereKey($country))
            ->first();
    }

    protected function getDefaultTaxZone(): ?TaxZone
    {
        return TaxZone::query()
            ->whereIsActive(true)
            ->whereIsDefault(true)
            ->first();
    }
}
