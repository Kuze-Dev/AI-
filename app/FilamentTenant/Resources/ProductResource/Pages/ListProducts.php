<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ProductResource\Pages;

use App\FilamentTenant\Resources\ProductResource;
use Domain\Product\Actions\CreateProductAction;
use Domain\Product\DataTransferObjects\ProductData;
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

    protected static function generateCombinations($row, $inputArray): array
    {
        $outputArray = [];
        foreach ($inputArray[0]['productOptionValues'] as $key => $optionValue1) {
            if (isset($inputArray[1]['productOptionValues'])) {
                foreach ($inputArray[1]['productOptionValues'] as $key2 => $optionValue2) {
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
                        'sku' => $row['sku'] . $key . $key2,
                    ];
                }
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
                    'sku' => $row['sku'] . $key,
                ];
            }
        }

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
                        $taxonomyTermIds = [];
                        foreach (['categories' => $data['categories'], 'brand' => $data['brand']] as $key => $taxonomyTerm) {
                            $taxonomy = Taxonomy::with('taxonomyTerms')
                                ->whereSlug($key)
                                ->first();

                            if ($taxonomy) {
                                $termModel = TaxonomyTerm::whereName($taxonomyTerm)->first();

                                if ( ! $termModel) {
                                    $termModel = TaxonomyTerm::create(
                                        [
                                            'name' => $taxonomyTerm,
                                            'taxonomy_id' => $taxonomy->id,
                                            'data' => [
                                                'main' => [
                                                    'heading' => $taxonomyTerm,
                                                ],
                                            ],
                                        ]
                                    );
                                }
                                array_push($taxonomyTermIds, $termModel->id);
                            }
                        }
                        $data['taxonomy_terms'] = $taxonomyTermIds;

                        unset($row, $data['categories'], $data['brand']);

                        $data['meta_data'] = new MetaDataData($data['name']);
                        $product = app(CreateProductAction::class)->execute(new ProductData(...$data));

                        return $product;
                    }
                )
                ->withValidation(
                    rules: [
                        'name' => 'required|string|min:3|max:100',
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
                        $product->status ? 'Active' : 'Inactive',
                        $product->is_digital_product ? 'Yes' : 'No',
                        $product->is_featured ? 'Yes' : 'No',
                        $product->is_special_offer ? 'Yes' : 'No',
                        $product->allow_customer_remarks ? 'Yes' : 'No',
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
