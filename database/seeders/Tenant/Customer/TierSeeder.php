<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Customer;

use Domain\Customer\Database\Factories\TierFactory;
use Illuminate\Database\Seeder;

class TierSeeder extends Seeder
{
    public function run(): void
    {
        TierFactory::new()->createOne(['name' => 'Default']);
    }
}
