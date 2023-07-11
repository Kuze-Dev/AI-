<?php

declare(strict_types=1);

namespace Domain\Address\Database\Factories;

use Domain\Address\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Address\Models\Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    /** @return array */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->word(),
            'name' => $this->faker->unique()->name(),
            'capital' => $this->faker->word(),
            'timezone' => $this->faker->timezone(),
            'active' => $this->faker->boolean(),
        ];
    }
}
