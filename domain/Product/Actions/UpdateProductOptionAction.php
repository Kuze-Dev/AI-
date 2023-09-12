<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\DataTransferObjects\ProductOptionData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;

class UpdateProductOptionAction
{
    public function execute(Product $product, ProductData $productData): array
    {
        $mutableVariants = $productData->product_variants ?? [];
        $mutableOptions = $productData->product_options;

        // Flush Product Option
        if ( ! filled($mutableOptions) && ! filled($mutableVariants)) {
            ProductOption::whereProductId($product->id)->delete();

            return [];
        }

        // Process Create or Update of Product Options and Option Values
        foreach ($mutableOptions ?? [] as $key => $productOption) {
            $optionModel = ProductOption::find($productOption->id);

            if ($optionModel instanceof ProductOption) {
                $optionModel->product_id = $product->id;
                $optionModel->name = $productOption->name;

                $optionModel->save();
            } else {
                $newOptionModel = ProductOption::create([
                    'product_id' => $product->id,
                    'name' => $productOption->name,
                ]);

                $mutableVariants = $this->searchAndChangeValue(
                    $productOption->id,
                    $mutableVariants,
                    $newOptionModel->id
                );

                $productOption = $productOption->withId($newOptionModel->id, $productOption);
            }

            $mutableOptionValues = $productOption->productOptionValues;

            // Process Create or Update of Product Option Value
            foreach ($mutableOptionValues as $key2 => $optionValue) {
                $optionValue = $optionValue->withOptionId($productOption->id, $optionValue);
                $proxyOptionValueId = $optionValue->id;

                $optionValueModel = ProductOptionValue::find($optionValue->id);

                if ($optionValueModel instanceof ProductOptionValue) {
                    $optionValueModel->name = $optionValue->name;
                    $optionValueModel->product_option_id = $productOption->id;

                    $optionValueModel->save();
                } else {
                    $newOptionValueModel = ProductOptionValue::create([
                        'name' => $optionValue->name,
                        'product_option_id' => $productOption->id,
                    ]);

                    $optionValue = $optionValue->withId($newOptionValueModel->id, $optionValue);

                    $mutableVariants = $this->searchAndChangeValue(
                        $proxyOptionValueId,
                        $mutableVariants,
                        $newOptionValueModel->id,
                        'option_value_id'
                    );
                }

                $mutableOptionValues[$key2] = $optionValue;
            }

            $productOption = $productOption->withProductOptionValues($mutableOptionValues, $productOption);

            $mutableOptions[$key] = $productOption;

            $this->sanitizeOptions($productOption);
        }

        $this->sanitizeOptions($productData, $product->id);

        return $mutableVariants;
    }

    protected function sanitizeOptions(ProductData|ProductOptionData $dtoData, ?int $productId = null): void
    {
        $arrayForMapping = [];

        if ($dtoData instanceof ProductData && $productId) {
            $arrayForMapping = $dtoData->product_options;
        }

        if ($dtoData instanceof ProductOptionData) {
            $arrayForMapping = $dtoData->productOptionValues;
        }

        // Removal of Product Options
        $mappedIds = collect($arrayForMapping)
            ->pluck('id')
            ->toArray();

        if (count($mappedIds)) {
            if ($dtoData instanceof ProductData && $productId) {
                ProductOption::where('product_id', $productId)
                    ->whereNotIn('id', $mappedIds)
                    ->get()
                    ->each(function ($option) {
                        $option->delete();
                    });
            }

            if ($dtoData instanceof ProductOptionData) {
                ProductOptionValue::where('product_option_id', $dtoData->id)
                    ->whereNotIn('id', $mappedIds)
                    ->get()
                    ->each(function ($optionValue) {
                        $optionValue->delete();
                    });
            }
        }
    }

    protected function searchAndChangeValue(string|int $needle, array $haystack, int $newValue, string $field = 'option_id'): array
    {
        return collect($haystack)->map(function ($variant) use ($needle, $newValue, $field) {
            /** @var array<int, \Domain\Product\DataTransferObjects\VariantCombinationData> $variantCombination */
            $variantCombination = $variant->combination;

            $newCombinations = collect($variantCombination)->map(function ($combination) use ($needle, $newValue, $field) {
                if ($combination->{$field} == $needle) {
                    if ($field == 'option_id') {
                        return $combination->withOptionId($newValue, $combination);
                    }

                    if ($field == 'option_value_id') {
                        return $combination->withOptionValueId($newValue, $combination);
                    }
                }

                return $combination;
            });

            return $variant->withCombination($newCombinations->toArray(), $variant);
        })->toArray();
    }
}
