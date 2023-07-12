<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Tier;

use Domain\Tier\Database\Factories\TierFactory;
use Illuminate\Database\Seeder;

class TierSeeder extends Seeder
{
    public function run(): void
    {
        TierFactory::new()
            ->sequence(
                ['name' => config('domain.tier.default')],
                ['name' => 'Gold'],
            )
            ->count(2)
            ->create();
    }
}
