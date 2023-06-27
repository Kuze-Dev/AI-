<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Address;

use Domain\Address\Models\City;
use Domain\Address\Models\Region;
use Domain\Address\Models\State;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    public function run(): void
    {

        $state = State::where('name', 'New York City')->first();
        $region = Region::first('name', 'National Capital Region')->first();

        if($state) {
            City::create([
                'state_id' => $state->id,
                'name' => 'New York City',
            ]);

            City::create([
                'state_id' => $state->id,
                'name' => 'Buffalo',
            ]);

            City::create([
                'state_id' => $state->id,
                'name' => 'Rochester',
            ]);
        }

        if($region) {
            City::create([
                'region_id' => $region->id,
                'name' => 'Manila',
            ]);

            City::create([
                'region_id' => $region->id,
                'name' => 'Quezon City',
            ]);

            City::create([
                'region_id' => $region->id,
                'name' => 'Makati City',
            ]);

        }

    }
}
