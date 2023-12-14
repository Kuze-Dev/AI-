<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Domain\Product\Actions\CreateProductAction;
use Domain\Product\Actions\UpdateProductAction;
use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use HalcyonAgile\FilamentImport\Actions\ImportAction;
use Illuminate\Validation\ValidationException;
use Log;
use Support\Common\Rules\MinimumValueRule;

class ImportProductAction
{
    public static function proceed(): ImportAction
    {
        return ImportAction::make('Product Import')
            ->translateLabel()
            ->uniqueBy('sku')
            ->processRowsUsing(fn (array $row): Product => self::processProductUpload($row))
            ->withValidation(
                rules: [
                    'product_id' => 'required|alpha_num|max:100',
                    'image_link' => 'nullable|url:http,https',
                    'name' => 'required|string|max:100',
                    'category' => 'required|string|max:100',
                    'brand' => 'required|string|max:100',
                    'sku' => 'required|string|unique:product_variants|max:100',
                    'stock' => ['required', 'numeric', new MinimumValueRule(0)],
                    'retail_price' => ['required', 'numeric', new MinimumValueRule(0.1)],
                    'selling_price' => ['required', 'numeric', new MinimumValueRule(0.1)],
                    'weight' => ['required', 'numeric', new MinimumValueRule(0.1)],
                    'length' => ['required', 'numeric', new MinimumValueRule(0.01)],
                    'width' => ['required', 'numeric', new MinimumValueRule(0.01)],
                    'height' => ['nullable', 'numeric', new MinimumValueRule(0.01)],
                ],
            );
    }

    public static function processProductUpload(array $row): Product
    {
        // Validate that the product has a maximum of 2 options
        self::validateIncomingProductOptions($row); // pwede comment

        // Map product options and its values
        $productOptions = self::mapProductOptions($row);

        // Generate combination for variants
        $productVariants = self::generateCombinations($row, $productOptions);

        // Prepare the data for the product
        $data = [
            'images' => [$row['image_link']],
            'name' => $row['name'],
            'categories' => $row['category'],
            'brand' => $row['brand'],
            'product_sku' => (string) $row['product_id'],
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

        // Collect taxonomy term IDs for the product
        $data['taxonomy_terms'] = self::collectTaxonomyTermIds($data); // pwede comment

        // Remove unnecessary data from the $row and $data arrays
        unset($row, $data['categories'], $data['brand']);

        // Set the meta data for the product
        $data['meta_data'] = ['title' => $data['name']];

        // Check if the product already exists in the database
        $foundProduct = Product::where('name', $data['name'])
            ->with('productOptions', 'productVariants', 'media')
            ->first();

        // If the product does not exist, create a new one
        if (! $foundProduct instanceof Product) {
            Log::info(
                'Import row(s) of product in CREATE ',
                [
                    'name' => $data['name'],
                    'product_id' => $data['product_sku'],
                    'sku' => $data['sku'],
                ]
            );

            return app(CreateProductAction::class)->execute(ProductData::fromCsv([
                ...$data,
                'sku' => $data['product_sku'],
            ]));
        }

        // Merge the product options and variants with the existing product
        $data['product_options'] = self::mergingProductOptions($foundProduct, $data['product_options']);

        $data['product_variants'] = self::mergingProductVariants($foundProduct, $data);

        // Check for possible sku duplication
        $foundProductViaSku = Product::where('sku', $data['product_sku'])->first();

        if ($foundProductViaSku instanceof Product && $foundProductViaSku->id != $foundProduct->id) {
            throw ValidationException::withMessages([
                'product_id' => trans("Product ID of {$data['name']} already exists."),
            ]);
        }

        $foundProductVariant = ProductVariant::where('sku', $data['sku'])->first();

        if ($foundProductVariant instanceof ProductVariant) {
            if ($foundProductVariant->product_id != $foundProduct->id) {
                throw ValidationException::withMessages([
                    'sku' => trans("SKU of {$data['name']} already exists."),
                ]);
            }

            if ($foundProductVariant->product_id == $foundProduct->id) {
                return $foundProduct;
            }
        }

        Log::info(
            'Import row(s) of product for UPDATE ',
            [
                'name' => $data['name'],
                'product_id' => $data['product_sku'],
                'sku' => $data['sku'],
            ]
        );

        if ($foundProduct->getMedia('image')->toArray()) {
            $data['skip_media_sync'] = true;
        }

        // Update the existing product
        return app(UpdateProductAction::class)->execute($foundProduct, ProductData::fromCsv([
            ...$data,
            'sku' => $data['product_sku'],
        ]));
    }

    protected static function validateIncomingProductOptions(array $row): void
    {
        for ($i = 1; $i <= 10; $i++) {
            if (
                isset($row["product_option_2_value_{$i}_image_link"])
                || isset($row["product_option_2_value_{$i}_icon_type"])
                || isset($row["product_option_2_value_{$i}_icon_value"])
            ) {
                throw ValidationException::withMessages([
                    'product_option_2_name' => trans("{$row['name']}'s option 2 must not have details related to icon and image customization"),
                ]);
            }
        }

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

            if (! $foundOption) {
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
                    'is_custom' => $item->is_custom,
                    'productOptionValues' => array_map(function ($optionValue) {
                        $optionValueModel = ProductOptionValue::
                            // with('media')
                            where('id', $optionValue['id'])
                                ->first();

                        if (! $optionValueModel) {
                            return [];
                        }

                        $toReturn = [
                            ...$optionValue,
                            ...$optionValue['data'],
                            // 'images' => $optionValueModel->getMedia('media')->pluck('uuid')->toArray(),
                        ];
                        unset($toReturn['data']);

                        return $toReturn;
                    }, $item->productOptionValues->toArray()),
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

                    $mappedExistingOptionValues = array_map(function ($optionValue) {
                        $optionValueModel = ProductOptionValue::
                            // with('media')
                            where('id', $optionValue['id'])
                                ->first();

                        if (! $optionValueModel) {
                            return [];
                        }

                        return [
                            ...$optionValue,
                            // 'images' => $optionValueModel->getMedia('media')->pluck('uuid')->toArray(),
                        ];
                    }, $existingOptions[$index]['productOptionValues']);

                    /** @var array $csvRowOptionValues */
                    $collectedCsvRowOptionValues = collect($csvRowOptionValues)
                        ->filter(function ($csvOptionValue) use ($mappedExistingOptionValues) {
                            $matchedElements = array_filter($mappedExistingOptionValues, function ($element) use ($csvOptionValue) {
                                return trim(strtolower($csvOptionValue['name'])) === trim(strtolower($element['name']));
                            });

                            return ! $matchedElements;
                        })
                        ->map(function ($optionValue) use ($existingOption) {
                            return [
                                ...$optionValue,
                                'product_option_id' => $existingOption['id'],
                            ];
                        })->toArray();

                    $existingOptions[$index]['productOptionValues'] =
                        array_merge(
                            $mappedExistingOptionValues,
                            $collectedCsvRowOptionValues,
                        );

                    $hasFound = true;

                    break; // Stop searching once found
                }
            }

            if (! $hasFound) {
                $existingOptions = array_merge($csvRowOptions, $existingOptions);
            }
        }

        return $existingOptions;
    }

    protected static function mergingProductVariants(Product $foundProduct, array $data): array
    {
        $productVariants = $data['product_variants'];

        $existingVariants = $foundProduct->productVariants->toArray();

        return array_merge($productVariants, $existingVariants);
    }

    protected static function newGenerateCombination(array $row, array $inputArray): array
    {

        return [];
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
                $option2Values->each(function ($optionValue2, $key2) use ($optionValue1, $row, $inputArray, &$outputArray) {
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
                        'sku' => $row['sku'],
                        // 'sku' => $row['sku'].$key1.$key2,
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
                    // 'sku' => $row['sku'].$keyOne,
                    'sku' => $row['sku'].$keyOne,
                    'status' => true,
                ];
            }
        });

        return $outputArray;
    }

    protected static function mapProductOptions(array $row): array
    {
        $productOptions = [];

        for ($i = 1; $i <= 2; $i++) {
            if (isset($row["product_option_{$i}_name"])) {
                /** Construct Option and Option Value */
                $productOption = [
                    'id' => uniqid(),
                    'name' => $row["product_option_{$i}_name"],
                    'slug' => $row["product_option_{$i}_name"],
                    'is_custom' => isset($row["product_option_{$i}_is_custom"])
                        && strtolower($row["product_option_{$i}_is_custom"])
                        === 'yes' ? true : false,
                    'productOptionValues' => [],
                ];

                $j = 1;
                while (isset($row["product_option_{$i}_value_{$j}"])) {
                    array_push($productOption['productOptionValues'], [
                        'id' => uniqid(),
                        'name' => $row["product_option_{$i}_value_{$j}"],
                        'slug' => $row["product_option_{$i}_value_{$j}"],
                        'icon_type' => $i === 1 ? (strtolower(str_replace(' ', '_', $row["product_option_{$i}_value_{$j}_icon_type"]))) : 'text',
                        'icon_value' => $i === 1 ? ($row["product_option_{$i}_value_{$j}_icon_value"] ?? '') : '',
                        // 'images' => $i === 1 ? (isset($row["product_option_{$i}_value_{$j}_image_link"]) ? [$row["product_option_{$i}_value_{$j}_image_link"]] : null) : [],
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

                if (! $termModel) {
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
