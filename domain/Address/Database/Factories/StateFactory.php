<?php

declare(strict_types=1);

namespace Domain\Address\Database\Factories;

use Domain\Address\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<State>
 */
class StateFactory extends Factory
{
    protected $model = State::class;

    public function definition(): array
    {
        return [
            'country_id' => CountryFactory::new(),
            'name' => $this->faker->name(),
        ];
    }
}
