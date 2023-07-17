<?php

declare(strict_types=1);

namespace Domain\Address\Database\Factories;

use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Address\Models\State>
 */
class StateFactory extends Factory
{
    protected $model = State::class;
    private static mixed $country_id = null;

    public function definition(): array
    {
        if (self::$country_id === null) {
            // Philippines
            self::$country_id = Country::whereName('Philippines')
                ->value('id')
                ?? CountryFactory::new();
        }

        return [
            'country_id' => self::$country_id,
            'name' => $this->faker->name(),
        ];
    }
}
