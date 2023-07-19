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

    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->word(),
            'subtitle' => 'Store Pickup',
            'description' => 'test shipping',
            'driver' => Driver::STORE_PICKUP,
            'ship_from_address' => [
                'address' => '185 BERRY ST',
                'state' => 'CA',
                'city' => 'SAN FRANCISCO',
                'zip3' => '94107',
                'zip4' => '1741',
            ],
        ];
    }
}
