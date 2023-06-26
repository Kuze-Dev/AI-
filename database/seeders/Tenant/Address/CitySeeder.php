<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Address;

use Domain\Address\Models\City;
use Domain\Address\Models\State;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {

        $state = State::first();

        if ( ! $state) {

            $country = \Domain\Address\Models\Country::first();

            if ( ! $country) {
                $country = \Domain\Address\Models\Country::create([
                    'code' => 'PH',
                    'name' => 'Philippines',
                    'capital' => 'Manila',
                    'timezone' => 'Asia/Manila',
                    'language' => 'Filipino',
                    'active' => true,
                ]);
            }

            $state = State::create([
                'country_id' => $country->id,
                'name' => 'Metro Manila',
            ]);
        }

        City::create([
            'state_id' => $state->id,
            'name' => 'Manila',
        ]);

        City::create([
            'state_id' => $state->id,
            'name' => 'Quezon City',
        ]);

        City::create([
            'state_id' => $state->id,
            'name' => 'Makati City',
        ]);

    }
}
