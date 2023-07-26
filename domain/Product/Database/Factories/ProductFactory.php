<?php

declare(strict_types=1);

namespace Domain\Product\Database\Factories;

use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Product\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

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
            // 'status',
            // 'is_digital_product',
            // 'is_featured',
            // 'is_special_offer',
            // 'allow_customer_remarks',
            // 'weight',
            // 'dimension',
            // 'minimum_order_quantity',
        ];
    }

    // private function seedProductOptions(Product $product, array $productOptions): void
    // {
    //     $combination = [];
    //     foreach ($productOptions as $key => $productOption) {
    //         $productOptionModel = ProductOption::create(['product_id' => $product->id, 'name' => $productOption['name']]);

    //         $optionValues = [];
    //         foreach ($productOption['values'] as $productOptionValue) {
    //             $optionValueModel = ProductOptionValue::create(['product_option_id' => $productOptionModel->id, 'name' => $productOptionValue]);

    //             array_push(
    //                 $optionValues,
    //                 [
    //                     'option_id' => $productOptionModel->id,
    //                     'option' => $productOptionModel->name,
    //                     'option_value_id' => $optionValueModel->id,
    //                     'option_value' => $optionValueModel->name,
    //                 ]
    //             );
    //         }
    //         array_push($combination, $optionValues);
    //     }

    //     $variantCombinations = [
    //         [
    //             $combination[0][0],
    //             $combination[1][0],
    //         ],
    //         [
    //             $combination[0][0],
    //             $combination[1][1],
    //         ],
    //     ];

    //     $this->seedProductVariants($product, $variantCombinations);
    // }

    // private function seedProductVariants(Product $product, array $variantCombinations): void
    // {
    //     collect($variantCombinations)->each(function ($combination, $index) use ($product) {
    //         ProductVariant::create([
    //             'product_id' => $product->id,
    //             'sku' => $product->sku . $index,
    //             'combination' => $combination,
    //             'retail_price' => $product->retail_price,
    //             'selling_price' => $product->selling_price,
    //             'stock' => $product->stock,
    //         ]);
    //     });
    // }
}
