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
        $customerFactory = CustomerFactory::new()
            ->recycle(
                Tier::whereName(config('domain.tier.default'))->first()
            )
            ->count(5);

        $customerFactory
            ->inactive()
            ->verified()
            ->create();

        $customerFactory
            ->inactive()
            ->unverified()
            ->create();

        $customerFactory
            ->active()
            ->verified()
            ->create();

        $customerFactory
            ->active()
            ->unverified()
            ->create();
    }
}
