<?php

declare(strict_types=1);

namespace Domain\Address\Actions;

use Domain\Address\DataTransferObjects\AddressData;
use Domain\Address\Models\Address;

class UpdateCountryAction
{
    public function execute(Country $country, CountryData $countryData): Country
    {

        $country->update([
            'code' => $countryData->code,
            'name' => $countryData->name,
            'capital' => $countryData->capital,
            'timezone' => $countryData->timezone,
            'language' => $countryData->language,
            'active' => $countryData->active,
        ]);

        return $country;
    }
}
