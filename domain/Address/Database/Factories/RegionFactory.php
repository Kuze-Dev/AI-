<?php

declare(strict_types=1);

namespace Domain\Address\Database\Factories;

use Domain\Address\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

class RegionFactory extends Factory
{
    protected $model = Region::class;

    public function definition(): array
    {
        return [
            'country_id' => CountryFactory::new(),
            'name' => $this->faker->name(),
        ];
    }
}
