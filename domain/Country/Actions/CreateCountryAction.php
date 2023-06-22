<?php

declare(strict_types=1);

namespace Domain\Country\Actions;

use Domain\Country\DataTransferObjects\CountryData;
use Domain\Country\Models\Country;

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
