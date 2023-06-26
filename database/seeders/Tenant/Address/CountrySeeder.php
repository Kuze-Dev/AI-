<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Address;


use Domain\Address\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Country::create([
            'code' => 'PH',
            'name' => 'Philippines',
            'capital' => 'Manila',
            'timezone' => 'Asia/Manila',
            'language' => 'Filipino',
            'active' => false,
        ]);

        Country::create([
            'code' => 'US',
            'name' => 'United States',
            'capital' => 'Washington, D.C.',
            'timezone' => 'America/New_York',
            'language' => 'English',
            'active' => false,
        ]);

        // Add more countries as needed
    }
}
