<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Address;

use Domain\Address\Database\Factories\CountryFactory;
use Domain\Address\Database\Factories\StateFactory;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        CountryFactory::new()
            ->has(
                StateFactory::new([
                    'name' => 'Metro Manila',
                ])
            )
            ->has(
                StateFactory::new([
                    'name' => 'Bulacan',
                ])
            )
            ->has(
                StateFactory::new([
                    'name' => 'Pampanga',
                ])
            )
            ->createOne([
                'code' => 'PH',
                'name' => 'Philippines',
                'capital' => 'Manila',
                'timezone' => 'Asia/Manila',
                'language' => 'Filipino',
                'active' => false,
            ]);

        CountryFactory::new()
            ->has(
                StateFactory::new([
                    'name' => 'New York City',
                ])
            )
            ->has(
                StateFactory::new([
                    'name' => 'Los Angeles',
                ])
            )
            ->has(
                StateFactory::new([
                    'name' => 'Chicago',
                ])
            )
            ->createOne([
                'code' => 'US',
                'name' => 'United States',
                'capital' => 'Washington, D.C.',
                'timezone' => 'America/New_York',
                'language' => 'English',
                'active' => false,
            ]);

    }
}
