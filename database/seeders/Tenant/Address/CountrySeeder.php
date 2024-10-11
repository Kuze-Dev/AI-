<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Address;

use Domain\Address\Models\Country;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CountrySeeder extends Seeder
{
    /** @throws Exception */
    public function run(): void
    {
        // $countries = $this->getCountryData();

        // $bar = $this->command->getOutput()->createProgressBar(count($countries));

        // foreach ($countries as $countryData) {
        //     $timezone = null;
        //     if (isset($countryData['timezones'][0]['gmtOffsetName'])) {
        //         $timezone = $countryData['timezones'][0]['gmtOffsetName'];
        //     }

        //     $country = Country::create([
        //         'code' => $countryData['iso2'],
        //         'name' => $countryData['name'],
        //         'capital' => $countryData['capital'],
        //         'timezone' => $timezone,
        //         'active' => false,
        //     ]);

        //     foreach ($countryData['states'] as $stateData) {
        //         $country->states()->create([
        //             'name' => $stateData['name'],
        //             'code' => $stateData['state_code'],
        //         ]);
        //     }

        //     $bar->advance();
        // }

        // $bar->finish();

        // $this->command->getOutput()->newLine();

        // $pathCountry = database_path('/seeders/Tenant/Country/countries.sql');
        // $pathCountryState = database_path('/seeders/Tenant/Country/states.sql');
        // // Load the SQL file content
        // $sql_c = File::get($pathCountry);
        // $sql_s = File::get($pathCountryState);
        // // Execute the SQL
        // DB::unprepared($sql_c);
        // DB::unprepared($sql_s);
    }

    /** @throws Exception */
    protected function getCountryData(): array
    {
        $response = file_get_contents('https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/countries%2Bstates.json');

        if (! $response) {
            throw new Exception();
        }

        return json_decode($response, true);
    }
}
