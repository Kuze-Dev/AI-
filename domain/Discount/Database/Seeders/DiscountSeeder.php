<?php

namespace Domain\Discount\Database\Seeders;

use Domain\Discount\Database\Factories\DiscountConditionFactory;
use Domain\Discount\Database\Factories\DiscountFactory;
use Domain\Discount\Database\Factories\DiscountRequirementFactory;
use Illuminate\Database\Seeder;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        DiscountFactory::new()
            ->has(DiscountConditionFactory::new())
            ->has(DiscountRequirementFactory::new())
            ->count(10)
            ->create();
    }
}
