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
        return [
            'country_id' => function (array $attributes) {

                if ( ! isset($attributes['country_id'])) {

                    if(self::$country_id === null) {
                        self::$country_id = Country::whereName('Philippines')
                            ->value('id')
                            ?? CountryFactory::new();
                    }

                    return self::$country_id;
                }

                return $attributes;
            },
            'name' => $this->faker->name(),
        ];
    }
}
