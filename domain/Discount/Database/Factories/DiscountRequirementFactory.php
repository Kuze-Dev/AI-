<?php

declare(strict_types=1);

namespace Domain\Discount\Database\Factories;

use Domain\Discount\Models\DiscountRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountRequirementFactory extends Factory
{
    protected $model = DiscountRequirement::class;

    public function definition(): array
    {
        return [
            'requirement_type' => 'minimum_order_amount',
            'minimum_amount' => fake()->randomNumber(),
        ];
    }
}
