<?php

declare(strict_types=1);

namespace Domain\Currency\Database\Factories;

use Domain\Currency\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->word(),
            'name' => $this->faker->name(),
            'exchange_rate' => $this->faker->boolean() ? $this->faker->randomFloat(2) : null,
            'enabled' => $this->faker->boolean(),
            'default' => $this->faker->boolean(),
        ];
    }
}
