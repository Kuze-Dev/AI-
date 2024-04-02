<?php

declare(strict_types=1);

namespace Domain\Discount\Database\Factories;

use Domain\Discount\Enums\DiscountRequirementType;
use Domain\Discount\Models\DiscountRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Discount\Models\DiscountRequirement>
 */
class DiscountRequirementFactory extends Factory
{
    protected $model = DiscountRequirement::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'requirement_type' => DiscountRequirementType::MINIMUM_ORDER_AMOUNT->value,
            'minimum_amount' => fake()->numberBetween(100, 1000),
        ];
    }
}
