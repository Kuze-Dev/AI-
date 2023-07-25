<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Address;

use Domain\Address\Models\Country;
use Illuminate\Database\Seeder;
use Exception;

class CountrySeeder extends Seeder
{
    /** @throws Exception */
    public function run(): void
    {
        $countries = $this->getCountryData();

        $bar = $this->command->getOutput()->createProgressBar(count($countries));

        foreach ($countries as $countryData) {
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

            $bar->advance();
        }

        $bar->finish();

        $this->command->getOutput()->newLine();
    }

    /** @throws Exception */
    protected function getCountryData(): array
    {
        $response = file_get_contents('https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/countries%2Bstates.json');

        if ( ! $response) {
            throw new Exception();
        }

        return json_decode($response, true);
    }
}
