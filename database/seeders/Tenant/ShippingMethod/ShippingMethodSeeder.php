<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\ShippingMethod;

use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Domain\ShippingMethod\Enums\Driver;
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
        $state = StateFactory::new(['name' => 'cali'])
            ->for(
                /** @phpstan-ignore-next-line  */
                Country::whereName('United States')
                    ->first()
            )->createOne();

        ShippingMethodFactory::new()
            ->createOne([
                'title' => 'USPS',
                'subtitle' => 'usps',
                'description' => 'test',
                'driver' => Driver::USPS,
                'shipper_country_id' => $state->country_id,
                'shipper_state_id' => $state->id,
                'shipper_address' => '185 BERRY ST',
                'shipper_city' => 'SAN FRANCISCO',
                'shipper_zipcode' => '94107',
                'active' => true,
            ]);

        ShippingMethodFactory::new()
            ->createOne([
                'title' => 'UPS',
                'subtitle' => 'ups',
                'description' => 'test',
                'driver' => Driver::UPS,
                'shipper_country_id' => $state->country_id,
                'shipper_state_id' => $state->id,
                'shipper_address' => '185 BERRY ST',
                'shipper_city' => 'SAN FRANCISCO',
                'shipper_zipcode' => '94107',
                'active' => true,
            ]);

        CustomerFactory::new()
            /** @phpstan-ignore-next-line  */
            ->for(Tier::first())
            ->active()
            ->verified()
            ->has(
                AddressFactory::new([
                    'address_line_1' => '7800 Toy ForestSimonisview, OK 95080-3523',
                    'city' => 'New Alysha',
                    'zip_code' => '67433',
                ])
                    ->defaultShipping(false)
                    ->defaultBilling(false)
                    ->for(

                        /** @phpstan-ignore-next-line  */
                        State::whereRelation(
                            'country',
                            'name',
                            'Philippines'
                        )
                            ->whereName('Calabarzon')
                            ->first()
                    )
            )
            ->has(
                AddressFactory::new([
                    'address_line_1' => '185 Berry Street',
                    'city' => 'San Francisco',
                    'zip_code' => '94107',
                ])
                    ->defaultShipping()
                    ->defaultBilling()
                    ->for(
                        StateFactory::new(['name' => 'CA'])
                            ->for(
                                /** @phpstan-ignore-next-line  */
                                Country::whereName('United States')
                                    ->first()
                            )
                    )
            )
            ->createOne([
                'first_name' => 'USPS',
                'last_name' => 'TEST ACCOUNT',
                'email' => 'usps-test@test.com',
                'password' => 'secret',
            ]);
    }
}
