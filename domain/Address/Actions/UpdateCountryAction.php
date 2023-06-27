<?php

declare(strict_types=1);

namespace Domain\Address\Actions;

use Domain\Address\DataTransferObjects\CountryData;
use Domain\Address\Models\Country;

class UpdateCountryAction
{
    public function execute(Country $country, CountryData $countryData): Country
    {

        $country->update([
            'code' => $countryData->code,
            'name' => $countryData->name,
            'capital' => $countryData->capital,
            'state_or_region' => $countryData->state_or_region,
            'timezone' => $countryData->timezone,
            'language' => $countryData->language,
            'active' => $countryData->active,
        ]);

        return $country;
    }
}
