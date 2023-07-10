<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Database\Factories;

use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\ShippingMethod\Models\ShippingMethod>
 */
class ShippingMethodFactory extends Factory
{
    /** Specify reference model. */
    protected $model = ShippingMethod::class;

    /** Define values of model instance. */
    public function definition(): array
    {
        $title = $this->faker->unique()->word();

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'subtitle' => 'Store Pickup',
            'description' => 'test shipping',
            'driver' => 'store-pickup',
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
