<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\Pages;

use App\FilamentTenant\Resources\ProductResource;
use Domain\Product\Actions\CreateProductAction;
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
use Support\MetaData\DataTransferObjects\MetaDataData;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected static function generateCombinations(array $row, array $inputArray): array
    {
        $outputArray = [];
        $firstOptionValues = $inputArray[0]['productOptionValues'] ?? [];
        $secondOptionValues = $inputArray[1]['productOptionValues'] ?? [];

        /** @var array<int, array> $firstOptionValues */
        $option1Values = collect($firstOptionValues);

        /** @var array<int, array> $secondOptionValues */
        $option2Values = collect($secondOptionValues);

        $option1Values->each(function ($optionValue1, $key1) use ($option2Values, $row, &$outputArray) {
            if ($option2Values->isNotEmpty()) {
                $option2Values->each(function ($optionValue2, $key2) use ($optionValue1, $key1, $row, &$outputArray) {
                    $combination = [
                        [
                            'option' => $optionValue1['name'],
                            'option_id' => $optionValue1['product_option_id'],
                            'option_value' => $optionValue1['name'],
                            'option_value_id' => $optionValue1['id'],
                        ],
                        [
                            'option' => $optionValue2['name'],
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
                    ];
                });
            } else {
                $combination = [
                    [
                        'option' => $optionValue1['name'],
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
                ];
            }
        });

        return $outputArray;
    }

    protected function getActions(): array
    {
        return [
            ImportAction::make()
                ->model(Product::class)
                ->processRowsUsing(
                    function (array $row): Product {
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

                        /** Create product variants */
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
                            'height' => $row['height'],
                            'product_options' => $productOptions,
                            'product_variants' => $productVariants,
                        ];

                        /** Taxonomies (Category & Product) */
                        $taxonomyKeys = ['categories', 'brand'];

                        $taxonomyTermIds = collect($taxonomyKeys)->flatMap(function ($key) use ($data) {
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

                        $data['taxonomy_terms'] = $taxonomyTermIds;

                        unset($row, $data['categories'], $data['brand']);

                        $data['meta_data'] = new MetaDataData($data['name']);
                        $product = app(CreateProductAction::class)->execute(new ProductData(...$data));

                        return $product;
                    }
                )
                ->withValidation(
                    rules: [
                        'image_link' => 'nullable|string',
                        'name' => 'required|unique:products|string|max:100',
                        'category' => 'required|string|max:100',
                        'brand' => 'required|string|max:100',
                        'sku' => 'required|unique:products|string|max:30',
                        'stock' => 'required|numeric',
                        'retail_price' => 'required|numeric',
                        'selling_price' => 'required|numeric',
                        'weight' => 'required|numeric',
                        'length' => 'required|numeric',
                        'width' => 'required|numeric',
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
}
