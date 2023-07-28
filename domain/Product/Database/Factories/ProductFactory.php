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
use Illuminate\Support\Facades\Http;

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
        ];
    }

    public function seedData(): void
    {
        $blueprint = Blueprint::where('name', 'Image with Heading Block Blueprint')->first();
        $data = (new ProductSeeder())->data();

        if ( ! $blueprint) {
            $blueprint = Blueprint::create($data['blueprint_for_taxonomy']);
        }

        $this->seedTaxonomies($data['taxonomies'], $blueprint);
        $this->seedProducts($data['products'], $data['product_options'], $data['variant_combinations']);
    }

    private function seedTaxonomies(array $taxonomies, Blueprint $blueprint): void
    {
        // Seed Brand and Category in Taxonomy
        foreach ($taxonomies as $taxonomyData) {
            if ($foundTaxonomy = Taxonomy::whereName($taxonomyData['name'])->first()) {
                $foundTaxonomy->delete();
            }
            $taxonomy = Taxonomy::create([
                'name' => $taxonomyData['name'],
                'blueprint_id' => $blueprint->id,
            ]);

            foreach ($taxonomyData['term'] as $termData) {
                TaxonomyTerm::create(['taxonomy_id' => $taxonomy->id, 'data' => [
                    'main' => [
                        'heading' => $termData['name'],
                    ],
                ], 'name' => $termData['name']]);
            }
        }
    }

    private function seedProducts(array $products, array $productOptions, array $variantCombinations): void
    {
        $taxonomyTermIds = TaxonomyTerm::whereIn('slug', ['brand-one', 'clothing'])->pluck('id');

        collect($products)->each(function ($productData) use ($taxonomyTermIds, $productOptions) {
            $productImageUrl = $productData['image_url'];
            unset($productData['image_url']);
            $product = Product::create($productData);
            // Just to clear residual images of previous product
            $product->clearMediaCollection('image');

            $response = Http::get($productImageUrl);
            if ($response->successful()) {
                $product
                    ->addMediaFromUrl($productImageUrl)
                    ->toMediaCollection('image');
            }

            $product->taxonomyTerms()->attach($taxonomyTermIds);

            $this->seedProductOptions($product, $productOptions);
        });
    }

    private function seedProductOptions(Product $product, array $productOptions): void
    {
        $combination = [];
        foreach ($productOptions as $key => $productOption) {
            $productOptionModel = ProductOption::create(['product_id' => $product->id, 'name' => $productOption['name']]);

            $optionValues = [];
            foreach ($productOption['values'] as $productOptionValue) {
                $optionValueModel = ProductOptionValue::create(['product_option_id' => $productOptionModel->id, 'name' => $productOptionValue]);

                array_push(
                    $optionValues,
                    [
                        'option_id' => $productOptionModel->id,
                        'option' => $productOptionModel->name,
                        'option_value_id' => $optionValueModel->id,
                        'option_value' => $optionValueModel->name,
                    ]
                );
            }
            array_push($combination, $optionValues);
        }

        $variantCombinations = [
            [
                $combination[0][0],
                $combination[1][0],
            ],
            [
                $combination[0][0],
                $combination[1][1],
            ],
        ];

        $this->seedProductVariants($product, $variantCombinations);
    }

    private function seedProductVariants(Product $product, array $variantCombinations): void
    {
        collect($variantCombinations)->each(function ($combination, $index) use ($product) {
            ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $product->sku . $index,
                'combination' => $combination,
                'retail_price' => $product->retail_price,
                'selling_price' => $product->selling_price,
                'stock' => $product->stock,
            ]);
        });
    }
}
