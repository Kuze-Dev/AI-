<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\Pages;

use App\FilamentTenant\Resources\ProductResource;
use Domain\Product\Actions\CreateProductAction;
use Domain\Product\Actions\UpdateProductAction;
use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Enums\Decision;
use Domain\Product\Enums\Status;
use Domain\Product\Models\Product;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Support\Excel\Actions\ExportAction;
use Support\Excel\Actions\ImportAction;
use Illuminate\Database\Eloquent\Builder;
use Support\Common\Rules\MinimumValueRule;
use Domain\Product\Models\ProductOption;
use Illuminate\Validation\ValidationException;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            ImportAction::make()
                ->model(Product::class)
                ->processRowsUsing(fn (array $row): Product => self::processProductRows($row))
                ->withValidation(
                    rules: [
                        'product_id' => 'required|string|max:100',
                        'image_link' => 'nullable|string',
                        'name' => 'required|string|max:100',
                        'category' => 'required|string|max:100',
                        'brand' => 'required|string|max:100',
                        'sku' => 'required|unique:product_variants|string|max:100',
                        'stock' => ['required', 'numeric', new MinimumValueRule(0)],
                        'retail_price' => ['required', 'numeric', new MinimumValueRule(0.1)],
                        'selling_price' => ['required', 'numeric', new MinimumValueRule(0.1)],
                        'weight' => ['required', 'numeric', new MinimumValueRule(0.1)],
                        'length' => ['required', 'numeric', new MinimumValueRule(1)],
                        'width' => ['required', 'numeric', new MinimumValueRule(1)],
                        'height' => ['nullable', 'numeric', new MinimumValueRule(1)],
                    ],
                ),
            ExportAction::make()
                ->model(Product::class)
                ->queue()
                ->query(fn (Builder $query) => $query->latest())
                ->mapUsing(
                    [
                        'Name', 'SKU', 'Description', 'Retail Price', 'Selling Price', 'Stock',
                        'Status', 'Is Digital Product', 'Is Featured', 'Is Special Offer',
                        'Allow Customer Remarks', 'Weight', 'Dimension', 'Minimum Order Quantity',
                        'Created At',
                    ],
                    fn (Product $product): array => [
                        $product->name,
                        $product->sku,
                        $product->description,
                        $product->retail_price,
                        $product->selling_price,
                        $product->stock,
                        $product->status ? Status::ACTIVE->value : STATUS::INACTIVE->value,
                        $product->is_digital_product ? Decision::YES->value : Decision::NO->value,
                        $product->is_featured ? Decision::YES->value : Decision::NO->value,
                        $product->is_special_offer ? Decision::YES->value : Decision::NO->value,
                        $product->allow_customer_remarks ? Decision::YES->value : Decision::NO->value,
                        $product->allow_stocks ? Decision::YES->value : Decision::NO->value,
                        $product->weight,
                        $product->dimension,
                        $product->minimum_order_quantity,
                        $product->created_at?->format(config('tables.date_time_format')),
                    ]
                ),
            Actions\CreateAction::make(),
        ];
    }

    protected static function processProductRows(array $row): Product
    {
        // Product must only have a maximum of 2 options
        self::validateIncomingProductOptions($row);

        // Mapping product options and its values
        $productOptions = self::remapProductOptions($row);

        // Generate combination for variants
        $productVariants = self::generateCombinations($row, $productOptions);
        $data = [
            'images' => $row['image_link'],
            'name' => $row['name'],
            'categories' => $row['category'],
            'brand' => $row['brand'],
            'product_sku' => $row['product_id'] ?? $row['sku'],
            'sku' => $row['sku'],
            'stock' => $row['stock'],
            'retail_price' => $row['retail_price'],
            'selling_price' => $row['selling_price'],
            'weight' => $row['weight'],
            'length' => $row['length'],
            'width' => $row['width'],
            'height' => $row['height'] ?? 1,
            'product_options' => $productOptions,
            'product_variants' => $productVariants,
        ];

        $data['taxonomy_terms'] = self::collectTaxonomyTermIds($data);

        unset($row, $data['categories'], $data['brand']);

        $data['meta_data'] = ['title' => $data['name']];

        $foundProduct = Product::where('name', $data['name'])
            ->with('productOptions', 'productVariants')
            ->first();

        if ( ! $foundProduct instanceof Product) {
            return app(CreateProductAction::class)->execute(ProductData::fromCsv([
                ...$data,
                'sku' => $data['product_sku'] ?? $data['sku'],
            ]));
        }

        $data['product_options'] = self::mergingProductOptions($foundProduct, $data['product_options']);
        $data['product_variants'] = self::mergingProductVariants($foundProduct, $data);

        return app(UpdateProductAction::class)->execute($foundProduct, ProductData::fromCsv([
            ...$data,
            'sku' => $data['product_sku'] ?? $data['sku'],
        ]));
    }

    protected static function validateIncomingProductOptions(array $row): void
    {
        $productOptions = ProductOption::select('id', 'name')
            ->where('product_id', function ($query) use ($row) {
                $query->select('id')
                    ->from('products')
                    ->where('name', $row['name'])
                    ->first();
            })
            ->distinct('name')
            ->get();

        if ($productOptions->count() > 1) {
            $foundOption = collect($productOptions)->first(function ($option) use ($row) {
                return $option['name'] === $row['product_option_1_name'];
            });

            if ( ! $foundOption) {
                throw ValidationException::withMessages([
                    'product_option_1_name' => trans("{$row['name']} must not exceed 2 product options."),
                ]);
            }
        }
    }

    protected static function mergingProductOptions(Product $foundProduct, array $csvRowOptions): array
    {
        $existingOptions = $foundProduct->productOptions->map(
            function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'productOptionValues' => $item->productOptionValues->toArray(),
                ];
            }
        )->toArray();

        foreach ($csvRowOptions as $csvRowOption) {
            $lowerCaseRowOptionName = strtolower($csvRowOption['name']);
            $hasFound = false;

            foreach ($existingOptions as $index => $existingOption) {
                if (
                    isset($existingOption['name'])
                    && strtolower($existingOption['name']) == $lowerCaseRowOptionName
                ) {
                    $csvRowOptionValues = $csvRowOption['productOptionValues'];

                    /** @var array $csvRowOptionValues */
                    $collectedCsvRowOptionValues = collect($csvRowOptionValues)
                        ->map(function ($optionValue) use ($existingOption) {
                            return [
                                ...$optionValue,
                                'product_option_id' => $existingOption['id'],
                            ];
                        })->toArray();

                    $existingOptions[$index]['productOptionValues'] =
                        array_merge(
                            $existingOptions[$index]['productOptionValues'],
                            $collectedCsvRowOptionValues,
                        );

                    $hasFound = true;

                    break; // Stop searching once found
                }
            }

            if ( ! $hasFound) {
                $existingOptions = array_merge($csvRowOptions, $existingOptions);
            }
        }

        return $existingOptions;
    }

    protected static function mergingProductVariants(Product $foundProduct, array $data): array
    {
        $productOptions = $data['product_options'];
        $productVariants = $data['product_variants'];

        // If first and second product option have 2 or more option values, generate cross combination
        if (
            isset($productOptions[1])
            && count($productOptions[0]) >= 2
            && count($productOptions[1]) >= 2
        ) {
            return self::generateCombinations($data, $productOptions);
        }

        $existingVariants = $foundProduct->productVariants->toArray();
        // Iterate through $inputVariants and $existingVariants

        foreach ($productVariants as &$inputVariant) {
            foreach ($existingVariants as $existingVariant) {
                foreach ($inputVariant['combination'] as &$inputCombination) {
                    // Check if the "option" matches
                    foreach ($existingVariant['combination'] as $existingCombination) {

                        if ($inputCombination['option'] == $existingCombination['option']) {
                            // Update the "option_id" from $existingVariants
                            $inputCombination['option_id'] = $existingCombination['option_id'];
                        }
                    }
                }
            }
        }

        return array_merge($productVariants, $existingVariants);
    }

    protected static function generateCombinations(array $row, array $inputArray): array
    {
        $outputArray = [];
        $firstOptionValues = $inputArray[0]['productOptionValues'] ?? [];
        $secondOptionValues = $inputArray[1]['productOptionValues'] ?? [];

        /** @var array<int, array> $firstOptionValues */
        $option1Values = collect($firstOptionValues);

        /** @var array<int, array> $secondOptionValues */
        $option2Values = collect($secondOptionValues);

        $option1Values->each(function ($optionValue1, $key1) use ($option2Values, $row, $inputArray, &$outputArray) {
            if ($option2Values->isNotEmpty()) {
                $option2Values->each(function ($optionValue2, $key2) use ($optionValue1, $key1, $row, $inputArray, &$outputArray) {
                    $combination = [
                        [
                            'option' => $inputArray[0]['name'],
                            'option_id' => $optionValue1['product_option_id'],
                            'option_value' => $optionValue1['name'],
                            'option_value_id' => $optionValue1['id'],
                        ],
                        [
                            'option' => $inputArray[1]['name'],
                            'option_id' => $optionValue2['product_option_id'],
                            'option_value' => $optionValue2['name'],
                            'option_value_id' => $optionValue2['id'],
                        ],
                    ];

                    $outputArray[] = [
                        'combination' => $combination,
                        'id' => uniqid(),
                        'selling_price' => $row['selling_price'],
                        'retail_price' => $row['retail_price'],
                        'stock' => $row['stock'],
                        'sku' => $row['sku'] . $key1 . $key2,
                        'status' => true,
                    ];
                });
            } else {
                $combination = [
                    [
                        'option' => $inputArray[0]['name'],
                        'option_id' => $optionValue1['product_option_id'],
                        'option_value' => $optionValue1['name'],
                        'option_value_id' => $optionValue1['id'],
                    ],
                ];

                $keyOne = $key1 ?: '';
                $outputArray[] = [
                    'combination' => $combination,
                    'id' => uniqid(),
                    'selling_price' => $row['selling_price'],
                    'retail_price' => $row['retail_price'],
                    'stock' => $row['stock'],
                    'sku' => $row['sku'] . $keyOne,
                    'status' => true,
                ];
            }
        });

        return $outputArray;
    }

    protected static function remapProductOptions(array $row): array
    {
        $productOptions = [];

        for ($i = 1; $i <= 2; $i++) {
            if (isset($row["product_option_{$i}_name"])) {
                /** Construct Option and Option Value */
                $productOption = [
                    'id' => uniqid(),
                    'name' => $row["product_option_{$i}_name"],
                    'slug' => $row["product_option_{$i}_name"],
                    'productOptionValues' => [],
                ];

                $j = 1;
                while (isset($row["product_option_{$i}_value_{$j}"])) {
                    array_push($productOption['productOptionValues'], [
                        'id' => uniqid(),
                        'name' => $row["product_option_{$i}_value_{$j}"],
                        'slug' => $row["product_option_{$i}_value_{$j}"],
                        'product_option_id' => $productOption['id'],
                    ]);

                    $j++;
                }
                array_push($productOptions, $productOption);
            }
        }

        return $productOptions;
    }

    protected static function collectTaxonomyTermIds(array $data): array
    {
        /** Taxonomies (Category & Product) */
        $taxonomyKeys = ['categories', 'brand'];

        return collect($taxonomyKeys)->flatMap(function ($key) use ($data) {
            $taxonomyTerm = $data[$key];

            $taxonomy = Taxonomy::with('taxonomyTerms')
                ->whereSlug($key)
                ->first();

            if ($taxonomy) {
                $termModel = TaxonomyTerm::whereName($taxonomyTerm)->first();

                if ( ! $termModel) {
                    $termModel = TaxonomyTerm::create([
                        'name' => $taxonomyTerm,
                        'taxonomy_id' => $taxonomy->id,
                        'data' => [
                            'main' => [
                                'heading' => $taxonomyTerm,
                            ],
                        ],
                    ]);
                }

                return [$termModel->id];
            }

            return [];
        })->toArray();
    }
}
