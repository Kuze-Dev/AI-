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

    #[\Override]
    public function definition()
    {
        return [
            // Define the fields of the Product model here
            'name' => $this->faker->name,
            'sku' => $this->faker->unique()->numerify('SKU###'),
            'description' => $this->faker->paragraph,
            'selling_price' => $this->faker->randomFloat(2, 0, 100),
            'retail_price' => $this->faker->randomFloat(2, 0, 100),
            'stock' => $this->faker->numberBetween(0, 100),
            'status' => $this->faker->boolean(),
            'is_digital_product' => $this->faker->boolean(),
            'is_featured' => $this->faker->boolean(),
            'is_special_offer' => $this->faker->boolean(),
            'allow_customer_remarks' => $this->faker->boolean(),
            'allow_stocks' => $this->faker->boolean(),
            'allow_guest_purchase' => $this->faker->boolean(),
            'weight' => $this->faker->numberBetween(0, 100),
            'dimension' => [
                'length' => $this->faker->numberBetween(0, 100),
                'width' => $this->faker->numberBetween(0, 100),
                'height' => $this->faker->numberBetween(0, 100),
            ],
            'minimum_order_quantity' => $this->faker->numberBetween(0, 10),
        ];
    }

    public function setStatus(bool $status): self
    {
        return $this->state(['status' => $status]);
    }

    public function setMinimumOrderQuantity(int $quantity): self
    {
        return $this->state(['minimum_order_quantity' => $quantity]);
    }
}
