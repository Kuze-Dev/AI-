<?php

declare(strict_types=1);

namespace Domain\Discount\Database\Factories;

use Domain\Discount\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Discount\Models\Discount>
 */
class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        return [
            'name' => fake()->firstName(),
            'slug' => fake()->firstName(),
            'description' => fake()->word(),
            'code' => fake()->unique()->word(),
            'max_uses' => 10,
            'status' => 'active',

            'valid_start_at' => fake()->dateTime(),
            'valid_end_at' => fake()->dateTime(),
        ];
    }
}
