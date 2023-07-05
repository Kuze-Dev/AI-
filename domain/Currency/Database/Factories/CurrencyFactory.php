<?php

declare(strict_types=1);

namespace Domain\Currency\Database\Factories;

use Domain\Currency\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Currency>
 */

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->word(),
            'name' => $this->faker->unique()->name(),
            'exchange_rate' => Arr::random(range(10, 100, 0.1222)),
            'enabled' => $this->faker->boolean(),
            'default' => $this->faker->boolean(),
        ];
    }
}
