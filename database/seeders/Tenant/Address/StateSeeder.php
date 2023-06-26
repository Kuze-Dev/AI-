<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Address;

use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Retrieve a country
        $country = Country::first();

        // Create states associated with the country
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
        // Add more states and countries as needed
    }
}
