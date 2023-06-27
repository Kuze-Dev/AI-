<?php

declare(strict_types=1);

namespace Domain\Address\Database\Factories;

use Domain\Address\Enums\CountryStateOrRegion;
use Domain\Address\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    /** @return array */
    public function definition(): array
    {
        return [
            'code' => $this->faker->word(),
            'name' => $this->faker->name(),
            'capital' => $this->faker->word(),
            'state_or_region' => Arr::random(CountryStateOrRegion::cases()),
            'timezone' => $this->faker->timezone(),
            'language' => $this->faker->word(),
            'active' => $this->faker->boolean(),
        ];
    }
}
