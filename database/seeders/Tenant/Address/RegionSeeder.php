<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Address;

use Domain\Address\Models\Country;
use Domain\Address\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    public function run(): void
    {

        $country = Country::where('code', 'PH')->first();

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

        Region::create([
            'country_id' => $country->id,
            'name' => 'National Capital Region',
        ]);
    
        Region::create([
            'country_id' => $country->id,
            'name' => 'Central Visayas',
        ]);
    
        Region::create([
            'country_id' => $country->id,
            'name' => 'Central Luzon',
        ]);



    }
}
