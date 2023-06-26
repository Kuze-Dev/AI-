<?php

declare(strict_types=1);

namespace Domain\Product\Database\Factories;

use Domain\Product\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Product\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word,
            'sku' => $this->faker->unique()->word,
            'description' => $this->faker->paragraph,
            'retail_price' => $this->faker->randomFloat(2, 10, 100),
            'selling_price' => $this->faker->randomFloat(2, 10, 100),
            'shipping_fee' => $this->faker->randomFloat(2, 10, 100),
            'stock' => $this->faker->randomNumber(),
        ];
    }
}
