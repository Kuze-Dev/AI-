<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Country;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('countries')->insert([
            [
                'code' => 'PH',
                'name' => 'Philippines',
                'capital' => 'Manila',
                'timezone' => 'Asia/Manila',
                'language' => 'Filipino',
                'active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'US',
                'name' => 'United States',
                'capital' => 'Washington, D.C.',
                'timezone' => 'America/New_York',
                'language' => 'English',
                'active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'JP',
                'name' => 'Japan',
                'capital' => 'Tokyo',
                'timezone' => 'Asia/Tokyo',
                'language' => 'Japanese',
                'active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'GB',
                'name' => 'United Kingdom',
                'capital' => 'London',
                'timezone' => 'Europe/London',
                'language' => 'English',
                'active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'AU',
                'name' => 'Australia',
                'capital' => 'Canberra',
                'timezone' => 'Australia/Sydney',
                'language' => 'English',
                'active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'CA',
                'name' => 'Canada',
                'capital' => 'Ottawa',
                'timezone' => 'America/Toronto',
                'language' => 'English',
                'active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'DE',
                'name' => 'Germany',
                'capital' => 'Berlin',
                'timezone' => 'Europe/Berlin',
                'language' => 'German',
                'active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'FR',
                'name' => 'France',
                'capital' => 'Paris',
                'timezone' => 'Europe/Paris',
                'language' => 'French',
                'active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'IT',
                'name' => 'Italy',
                'capital' => 'Rome',
                'timezone' => 'Europe/Rome',
                'language' => 'Italian',
                'active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ES',
                'name' => 'Spain',
                'capital' => 'Madrid',
                'timezone' => 'Europe/Madrid',
                'language' => 'Spanish',
                'active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
