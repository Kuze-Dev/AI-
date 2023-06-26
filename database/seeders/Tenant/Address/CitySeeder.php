<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Address;

use Domain\Address\Models\City;
use Domain\Address\Models\State;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Retrieve a state
        $state = State::first();

        // Create cities associated with the state
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
        
        // Add more cities and states as needed
    }
}
