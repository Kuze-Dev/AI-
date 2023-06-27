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
            'state_id' => StateFactory::new(),
            'region_id' => RegionFactory::new(),
            'name' => $this->faker->name(),
        ];
    }
}
