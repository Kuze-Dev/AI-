<?php

declare(strict_types=1);

namespace Domain\Product\Database\Factories;

use Domain\Product\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;


class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition()
    {
        $colors = ['black', 'white', 'red', 'blue', 'green'];
        $sizes = ['small', 'medium', 'large', 'x-large'];

        return [
            'product_id' => $this->faker->numberBetween(1, 5),
            'sku' => $this->faker->unique()->numerify('SKU###'),
            'combination' => [
                'color' => $this->faker->randomElement($colors),
                'size' => $this->faker->randomElement($sizes),
            ],
            'retail_price' => $this->faker->randomFloat(2, 0, 100),
            'selling_price' => $this->faker->randomFloat(2, 0, 100),
            'stock' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function setProductId(int $id): self
    {
        return $this->state(['product_id' => $id]);
    }
}
