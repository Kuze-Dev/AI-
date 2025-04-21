<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Database\Factories;

use Domain\ShippingMethod\Enums\Driver;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\ShippingMethod\Models\ShippingMethod>
 */
class ShippingMethodFactory extends Factory
{
    protected $model = ShippingMethod::class;

    #[\Override]
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->word(),
            'subtitle' => 'Store Pickup',
            'description' => 'test shipping',
            'driver' => Driver::STORE_PICKUP,
            'shipper_country_id' => '236',
            'shipper_state_id' => '4839',
            'shipper_address' => '185 BERRY ST',
            'shipper_city' => 'SAN FRANCISCO',
            'shipper_zipcode' => '94107',
            'active' => $this->faker->boolean,
        ];
    }
}
