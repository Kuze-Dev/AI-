<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Address;

use Domain\Address\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        Http::fake([
            'https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/countries%2Bstates.json' => Http::response([
                [
                    "name" => "Philippines",
                    "iso3" => "PHL",
                    "iso2" => "PH",
                    "numeric_code" => "608",
                    "phone_code" => "63",
                    "capital" => "Manila",
                    "currency" => "PHP",
                    "currency_name" => "Philippine peso",
                    "currency_symbol" => "₱",
                    "tld" => ".ph",
                    "native" => "Pilipinas",
                    "region" => "Asia",
                    "subregion" => "South-Eastern Asia",
                    "timezones" => [
                        [
                            "zoneName" => "Asia/Manila",
                            "gmtOffset" => 28800,
                            "gmtOffsetName" => "UTC+08:00",
                            "abbreviation" => "PHT",
                            "tzName" => "Philippine Time",
                        ],
                    ],
                    "states" => [
                        [
                            "id" => 1324,
                            "name" => "Abra",
                            "state_code" => "ABR",
                            "latitude" => "42.49708300",
                            "longitude" => "-96.38441000",
                            "type" => "province",
                        ],
                        [
                            "id" => 1323,
                            "name" => "Agusan del Norte",
                            "state_code" => "AGN",
                            "latitude" => "8.94562590",
                            "longitude" => "125.53192340",
                            "type" => "province",
                        ],
                        [
                            "id" => 1326,
                            "name" => "Agusan del Sur",
                            "state_code" => "AGS",
                            "latitude" => "8.04638880",
                            "longitude" => "126.06153840",
                            "type" => "province",
                        ],
                        [
                            "id" => 1331,
                            "name" => "Aklan",
                            "state_code" => "AKL",
                            "latitude" => "11.81661090",
                            "longitude" => "122.09415410",
                            "type" => "province",
                        ],
                        [
                            "id" => 1337,
                            "name" => "Albay",
                            "state_code" => "ALB",
                            "latitude" => "13.17748270",
                            "longitude" => "123.52800720",
                            "type" => "province",
                        ],
                        [
                            "id" => 1347,
                            "name" => "Metro Manila",
                            "state_code" => "NCR",
                            "latitude" => "14.60905370",
                            "longitude" => "121.02225650",
                            "type" => "province",
                        ],
                    ],
                ],
            ]),
        ]);
        

        $response = Http::get('https://raw.githubusercontent.com/dr5hn/countries-states-cities-database/master/countries%2Bstates.json');
        $countriesData = $response->json();

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
