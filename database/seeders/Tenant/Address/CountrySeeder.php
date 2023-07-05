<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Address;

use Domain\Address\Models\Country;
use Illuminate\Database\Seeder;
use Exception;

class CountrySeeder extends Seeder
{
    public function run(): void
    {

        $countriesData = $this->getCountriesData();

        if ($countriesData) {
            foreach ($countriesData as $countryData) {
                $country = Country::create([
                    'code' => $countryData['iso2'],
                    'name' => $countryData['name'],
                    'capital' => $countryData['capital'],
                    'timezone' => $countryData['timezones'][0]['gmtOffsetName'],
                    'active' => false,
                ]);

                foreach ($countryData['states'] as $stateData) {
                    $country->states()->create([
                        'name' => $stateData['name'],
                    ]);
                }
            }
        }
    }

    protected function getCountriesData(): array
    {
        $response = file_get_contents('https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/countries%2Bstates.json');

        if ( ! $response) {
            throw new Exception();
        }

        return json_decode($response, true);
    }
}
