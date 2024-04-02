<?php

declare(strict_types=1);

namespace Domain\Discount\Database\Factories;

use Domain\Discount\Models\DiscountCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Discount\Models\DiscountCondition>
 */
class DiscountConditionFactory extends Factory
{
    protected $model = DiscountCondition::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'discount_type' => fake()->randomElement(['order_sub_total', 'delivery_fee']),
            'amount_type' => fake()->randomElement(['fixed_value', 'percentage']),
            'amount' => fake()->numberBetween(1, 100),
        ];
    }
}
