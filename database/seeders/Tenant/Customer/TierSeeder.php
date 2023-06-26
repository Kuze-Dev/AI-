<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Customer;

use Domain\Tier\Database\Factories\TierFactory;
use Illuminate\Database\Seeder;

class TierSeeder extends Seeder
{
    public function run(): void
    {
        TierFactory::new()
            ->sequence(
                ['name' => 'Default'],
                ['name' => 'Gold'],
            )
            ->count(2)
            ->create();
    }
}
