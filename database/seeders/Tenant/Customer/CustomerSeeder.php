<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Customer;

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Tier\Models\Tier;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        /** @var \Domain\Tier\Models\Tier $tier */
        $tier = Tier::whereName(config('domain.tier.default'))->first();

        $customerFactory = CustomerFactory::new(['password' => 'secret'])
            ->recycle($tier)
            ->count(2);

        $customerFactory
            ->banned()
            ->unverified()
            ->create();

        $customerFactory
            ->banned()
            ->verified()
            ->create();

        $customerFactory
            ->inactive()
            ->unverified()
            ->create();

        $customerFactory
            ->inactive()
            ->verified()
            ->create();

        $customerFactory
            ->active()
            ->unverified()
            ->create();

        $customerFactory
            ->active()
            ->verified()
            ->create();
    }
}
