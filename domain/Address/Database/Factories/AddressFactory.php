<?php

declare(strict_types=1);

namespace Domain\Address\Database\Factories;

use Domain\Address\Models\Address;
use Domain\Customer\Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Address\Models\Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'customer_id' => CustomerFactory::new(),
            'country_id' => CountryFactory::new(),
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
            'city_id' => CityFactory::new(),
            'address_line_1' => $this->faker->address(),
            'address_line_2' => $this->faker->boolean() ? $this->faker->address() : null,
            'zip_code' => $this->faker->postcode(),
            'is_default_shipping' => $this->faker->boolean(),
            'is_default_billing' => $this->faker->boolean(),
        ];
    }

    public function defaultShipping(bool $state = true): self
    {
        return $this->state(['is_default_shipping' => $state]);
    }

    public function defaultBilling(bool $state = true): self
    {
        return $this->state(['is_default_billing' => $state]);
    }
}
