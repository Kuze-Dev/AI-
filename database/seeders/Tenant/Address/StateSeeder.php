<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Address;

use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    public function run(): void
    {

        $country = Country::first();

        if ( ! $country) {
            $country = Country::create([
                'code' => 'PH',
                'name' => 'Philippines',
                'capital' => 'Manila',
                'timezone' => 'Asia/Manila',
                'language' => 'Filipino',
                'active' => true,
            ]);
        }

        State::create([
            'country_id' => $country->id,
            'name' => 'Metro Manila',
        ]);

        State::create([
            'country_id' => $country->id,
            'name' => 'Cebu',
        ]);

        State::create([
            'country_id' => $country->id,
            'name' => 'Bulacan',
        ]);

    }
}
