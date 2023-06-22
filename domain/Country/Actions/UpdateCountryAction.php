<?php

declare(strict_types=1);

namespace Domain\Country\Actions;

use Domain\Country\DataTransferObjects\CountryData;
use Domain\Country\Models\Country;

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
