<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Address;

use Domain\Address\Database\Factories\CityFactory;
use Domain\Address\Database\Factories\CountryFactory;
use Domain\Address\Database\Factories\RegionFactory;
use Domain\Address\Database\Factories\StateFactory;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    public function run(): void
    {
        CountryFactory::new()
            ->has(
                RegionFactory::new([
                    'name' => 'National Capital Region',
                ])
                    ->has(
                        CityFactory::new()
                            ->count(3)
                            ->sequence(
                                [
                                    'name' => 'Manila',
                                ],
                                [
                                    'name' => 'Quezon City',
                                ],
                                [
                                    'name' => 'Makati City',
                                ],
                            )
                    )
            )
            ->has(
                RegionFactory::new([
                    'name' => 'Central Visayas',
                ])
            )
            ->has(
                RegionFactory::new([
                    'name' => 'Central Luzon',
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
                    ->has(
                        CityFactory::new()
                            ->count(3)
                            ->sequence(
                                [
                                    'name' => 'New York City',
                                ],
                                [
                                    'name' => 'Buffalo',
                                ],
                                [
                                    'name' => 'Rochester',
                                ],
                            )
                    )
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
