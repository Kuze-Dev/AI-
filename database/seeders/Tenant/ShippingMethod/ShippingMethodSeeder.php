<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\ShippingMethod;

use Illuminate\Database\Seeder;
use Domain\Address\Database\Factories\AddressFactory;
use Domain\Address\Database\Factories\StateFactory;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Tier\Models\Tier;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        


        CustomerFactory::new()
            ->has(
                AddressFactory::new([
                    'address_line_1' => '7800 Toy ForestSimonisview, OK 95080-3523',
                    'city' => 'New Alysha',
                    'zip_code' => '67433',
                ])
                    ->for(
                        State::whereRelation(
                            'country',
                            'name',
                            'Philippines'
                        )
                            ->whereName('Calabarzon')->first()
                    )
            )
            ->has(
                AddressFactory::new([
                    'address_line_1' => '185 Berry Street',
                    'city' => 'San Francisco',
                    'zip_code' => '4545',
                ])
                    ->has(
                        StateFactory::new(['name' => 'CA'])
                            ->for(
                                Country::whereName('United States')
                                    ->first()
                            )
                    )
            )
            ->for(Tier::first())
            ->active()
            ->verified()
            ->createOne([
                'email' => 'usps-test@test.com',
                'password' => 'secret',
            ]);
    }
}
