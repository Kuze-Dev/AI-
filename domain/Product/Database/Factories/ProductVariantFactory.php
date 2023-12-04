<?php

declare(strict_types=1);

namespace Domain\Product\Database\Factories;

use Domain\Product\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Product\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition()
    {
        return [
            'product_id' => ProductFactory::new(),
            'sku' => $this->faker->unique()->numerify('SKU###'),
            'combination' => [],
            'retail_price' => $this->faker->randomFloat(2, 0, 100),
            'selling_price' => $this->faker->randomFloat(2, 0, 100),
            'stock' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function setCombination(): self
    {
        $colors = ['black', 'white', 'red', 'blue', 'green'];
        $sizes = ['small', 'medium', 'large', 'x-large'];

        $combinations = [];

        foreach (['color' => $colors, 'size' => $sizes] as $key => $optionValues) {
            $option = ProductOptionFactory::new(['name' => $key])
                ->has(
                    ProductOptionValueFactory::new(['name' => $this->faker->randomElement($optionValues)])
                )
                ->createOne()->load('productOptionValues');

            array_push($combinations, [
                'option' => $option->name,
                'option_id' => $option->id,
                'option_value' => $option->productOptionValues()->first()?->name,
                'option_value_id' => $option->productOptionValues()->first()?->id,
            ]);
        }

        return $this->state(['combination' => $combinations]);
    }
}
