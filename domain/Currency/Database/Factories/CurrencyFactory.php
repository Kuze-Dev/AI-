<?php

declare(strict_types=1);

namespace Domain\Currency\Database\Factories;

use Domain\Currency\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->word(),
            'name' => $this->faker->unique()->name(),
            'symbol' => $this->faker->unique()->name(),
            'enabled' => $this->faker->boolean(),
        ];
    }
}
