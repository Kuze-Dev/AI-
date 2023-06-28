<?php

declare(strict_types=1);

namespace Domain\Product\Database\Factories;

use Database\Seeders\Tenant\Product\ProductSeeder;
use Domain\Blueprint\Models\Blueprint;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Factories\Factory;

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
            'shipping_fee' => $this->faker->randomFloat(2, 0, 10),
            'stock' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function seedData()
    {
        $blueprint = Blueprint::where('name', 'Image with Heading Block Blueprint')->first();
        $data = (new ProductSeeder())->data();

        $this->seedTaxonomies($data['taxonomies'], $blueprint);
        $this->seedProducts($data['products'], $data['product_options'], $data['variant_combinations']);
    }

    private function seedTaxonomies($taxonomies, $blueprint)
    {
        collect($taxonomies)->each(function ($taxonomyData) use ($blueprint) {
            if ( ! Taxonomy::whereName($taxonomyData['name'])->first()) {
                $taxonomy = Taxonomy::create([
                    'name' => $taxonomyData['name'],
                    'blueprint_id' => $blueprint->id,
                ]);

                collect($taxonomyData['term'])->each(function ($termData) use ($taxonomy) {
                    TaxonomyTerm::create([
                        'taxonomy_id' => $taxonomy->id,
                        'data' => [
                            'main' => [
                                'heading' => $termData['name'],
                            ],
                        ],
                        'name' => $termData['name'],
                    ]);
                });
            }
        });
    }

    private function seedProducts($products, $productOptions, $variantCombinations)
    {
        $taxonomyTermIds = TaxonomyTerm::whereIn('slug', ['brand-one', 'clothing'])->pluck('id');

        collect($products)->each(function ($productData) use ($taxonomyTermIds, $productOptions, $variantCombinations) {
            $product = Product::create($productData);
            $product->taxonomyTerms()->attach($taxonomyTermIds);

            $this->seedProductOptions($product, $productOptions);
            $this->seedProductVariants($product, $variantCombinations);
        });
    }

    private function seedProductOptions($product, $productOptions)
    {
        collect($productOptions)->each(function ($productOption) use ($product) {
            $productOptionModel = ProductOption::create([
                'product_id' => $product->id,
                'name' => $productOption['name'],
            ]);

            collect($productOption['values'])->each(function ($productOptionValue) use ($productOptionModel) {
                ProductOptionValue::create([
                    'product_option_id' => $productOptionModel->id,
                    'name' => $productOptionValue,
                ]);
            });
        });
    }

    private function seedProductVariants($product, $variantCombinations)
    {
        collect($variantCombinations)->each(function ($combination, $index) use ($product) {
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $product->sku . $index,
                'combination' => json_encode($combination),
                'retail_price' => $product->retail_price,
                'selling_price' => $product->selling_price,
                'stock' => $product->stock,
            ]);
        });
    }
}
