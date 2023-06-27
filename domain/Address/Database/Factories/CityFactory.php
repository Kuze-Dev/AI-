<?php

declare(strict_types=1);

namespace Domain\Address\Database\Factories;

use Domain\Address\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        return [
            'state_id' => function (array $attributes) {
                if (isset($attributes['region_id'])) {
                    return null;
                }

                return StateFactory::new();
            },
            'region_id' => function (array $attributes) {
                if (isset($attributes['state_id'])) {
                    return null;
                }

                return RegionFactory::new();
            },
            'name' => $this->faker->name(),
        ];
    }
}
