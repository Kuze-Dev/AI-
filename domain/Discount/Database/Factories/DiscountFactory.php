<?php

declare(strict_types=1);

namespace Domain\Discount\Database\Factories;

use Domain\Discount\Actions\AutoGenerateCode;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Discount\Models\Discount>
 */
class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    #[\Override]
    public function definition(): array
    {
        $name = fake()->firstName();

        return [
            'name' => $name,
            'slug' => $name,
            'description' => fake()->word(),
            'code' => new AutoGenerateCode(),
            'max_uses' => 10,
            'status' => DiscountStatus::ACTIVE,

            'valid_start_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'valid_end_at' => fake()->dateTimeBetween('now', '+1 month'),
        ];
    }

    public function deleted(): self
    {
        return $this->state(['deleted_at' => now()]);
    }
}
