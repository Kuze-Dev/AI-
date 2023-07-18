<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Customer;

use Domain\Address\Database\Factories\AddressFactory;
use Domain\Address\Models\State;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Tier\Models\Tier;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        /** @var \Domain\Tier\Models\Tier $tier */
        $tier = Tier::whereName(config('domain.tier.default'))->first();

        $states = State::whereRelation(
            'country',
            'name',
            'Philippines'
        )
            ->get();

        $randomAddress = fn () => AddressFactory::new()
            ->for($states->random())
            ->defaultShipping()
            ->defaultBilling();

        $customerFactory = CustomerFactory::new(['password' => 'secret'])
            ->recycle($tier)
            ->count(2);

        $customerFactory
            ->has($randomAddress())
            ->banned()
            ->unverified()
            ->create();

        $customerFactory
            ->has($randomAddress())
            ->banned()
            ->verified()
            ->create();

        $customerFactory
            ->has($randomAddress())
            ->inactive()
            ->unverified()
            ->create();

        $customerFactory
            ->has($randomAddress())
            ->inactive()
            ->verified()
            ->create();

        $customerFactory
            ->has($randomAddress())
            ->active()
            ->unverified()
            ->create();

        $customerFactory
            ->has($randomAddress())
            ->active()
            ->verified()
            ->create();
    }
}
