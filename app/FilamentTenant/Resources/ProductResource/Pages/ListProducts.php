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
                        'image_link' => 'nullable|string',
                        'name' => 'required|string|max:100',
                        'category' => 'required|string|max:100',
                        'brand' => 'required|string|max:100',
                        'sku' => 'required|unique:products|string|max:30',
                        'stock' => 'required|numeric',
                        'retail_price' => 'required|numeric',
                        'selling_price' => 'required|numeric',
                        'weight' => 'required|numeric',
                        'length' => 'required|numeric',
                        'width' => 'required|numeric',
                        // custom rule for sku
                        // custom rule for more than 2 options
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
        // Mapping product options and its values
        $productOptions = self::remapProductOptions($row);

        // Generate combination for variants
        $productVariants = self::generateCombinations($row, $productOptions);

        $data = [
            'images' => $row['image_link'],
            'name' => $row['name'],
            'categories' => $row['category'],
            'brand' => $row['brand'],
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

        if ( ! $foundProduct) {
            return app(CreateProductAction::class)->execute(ProductData::fromCsv($data));
        }

        $data['product_options'] = self::mergingProductOptions($foundProduct, $data['product_options']);

        $data['product_variants'] = self::mergingProductVariants($foundProduct, $data);

        return app(UpdateProductAction::class)->execute($foundProduct, ProductData::fromCsv($data));
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

        return array_merge($productVariants, $foundProduct->productVariants->toArray());
    }

    protected static function mergingProductOptions(Product $foundProduct, array $dataProductOptions): array
    {
        $collectedOptions = $foundProduct->productOptions->map(
            function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'productOptionValues' => $item->productOptionValues->toArray(),
                ];
            }
        )->toArray();

        foreach ($dataProductOptions as $dataProductOption) {
            $optionName = strtolower($dataProductOption['name']);
            $hasFound = false;
            foreach ($collectedOptions as $index => $item) {
                if (isset($item['name']) && strtolower($item['name']) == $optionName) {
                    $mappedDataProductOptionValues = collect($dataProductOption['productOptionValues'])
                        ->map(function ($optionValue) use ($item) {
                            return [
                                ...$optionValue,
                                'product_option_id' => $item['id'],
                            ];
                        })->toArray();

                    $collectedOptions[$index]['productOptionValues'] =
                        array_merge(
                            $collectedOptions[$index]['productOptionValues'],
                            $mappedDataProductOptionValues,
                        );

                    $hasFound = true;

                    break; // Stop searching once found
                }
            }

            if ( ! $hasFound) {
                dd($dataProductOptions, $collectedOptions);
                $collectedOptions = array_merge($dataProductOptions, $collectedOptions);
            }
        }

        return $collectedOptions;
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

        // \Log::info('here: ', [$inputArray]);

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

                $outputArray[] = [
                    'combination' => $combination,
                    'id' => uniqid(),
                    'selling_price' => $row['selling_price'],
                    'retail_price' => $row['retail_price'],
                    'stock' => $row['stock'],
                    'sku' => $row['sku'] . $key1,
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
