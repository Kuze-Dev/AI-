<?php

declare(strict_types=1);

namespace Domain\Address\Actions;

use Domain\Address\DataTransferObjects\AddressData;
use Domain\Address\Models\Address;

class CreateCountryAction
{
    public function execute(CountryData $countryData): Country
    {
        return Country::create([
            'code' => $countryData->code,
            'name' => $countryData->name,
            'capital' => $countryData->capital,
            'timezone' => $countryData->timezone,
            'language' => $countryData->language,
            'active' => $countryData->active,
        ]);
    }
}
