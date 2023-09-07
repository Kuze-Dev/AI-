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

        // Create or Update product options & update mutable variants for variant insertion
        collect($mutableOptions)->map(function (ProductOptionData $productOption, int $key) use ($product, $mutableVariants) {
            $optionModel = ProductOption::find($productOption->id);

            $newOptionModel = $this->createOrUpdateOption($optionModel, $product->id, $productOption->name);

            if ( ! $optionModel instanceof ProductOption) {
                $mutableVariants = $this->searchAndChangeValue($productOption->id, $mutableVariants, $newOptionModel->id);

                $productOption = $productOption->withId($newOptionModel->id, $productOption);
            }

            $mutableOptionValues = $productOption->productOptionValues;

            // Create Or Update of Product Option Value
            $collectedOptionValues = collect($mutableOptionValues)
                ->map(function ($optionValue, $key2) use ($productOption, $mutableVariants) {
                    $optionValue = $optionValue->withOptionId($productOption->id, $optionValue);

                    $proxyOptionValueId = $optionValue->id;

                    $optionValueModel = ProductOptionValue::find($optionValue->id);

                    /** @var ProductOptionValue|null $optionValueModel */
                    $newOptionValueModel = $this->createOrUpdateOptionValue(
                        $optionValueModel,
                        (int) $productOption->id,
                        $optionValue->name
                    );

                    if ( ! $optionValueModel instanceof ProductOptionValue) {
                        $optionValue = $optionValue->withId($newOptionValueModel->id, $optionValue);

                        $mutableVariants = $this->searchAndChangeValue(
                            $proxyOptionValueId,
                            $mutableVariants,
                            $newOptionValueModel->id,
                            'option_value_id'
                        );
                    }

                    return $optionValue;
                })->toArray();

            $productOption = $productOption->withProductOptionValues($collectedOptionValues, $productOption);

            $mutableOptions[$key] = $productOption;

            $this->sanitizeOptions($productOption);
        });

        $this->sanitizeOptions($productData, $product->id);

        return $mutableVariants;
    }

    protected function createOrUpdateOption(
        ProductOption|null $productOptionModel,
        int $productId,
        string $productOptionName,
    ): ProductOption {

        if ($productOptionModel instanceof ProductOption) {
            $productOptionModel->product_id = $productId;
            $productOptionModel->name = $productOptionName;

            $productOptionModel->save();
        } else {
            $productOptionModel = ProductOption::create([
                'product_id' => $productId,
                'name' => $productOptionName,
            ]);
        }

        return $productOptionModel;
    }

    protected function createOrUpdateOptionValue(
        ?ProductOptionValue $optionValueModel = null,
        int $productOptionId,
        string $optionValueName
    ): ProductOptionValue {

        if ($optionValueModel instanceof ProductOptionValue) {
            $optionValueModel->name = $optionValueName;
            $optionValueModel->product_option_id = $productOptionId;

            $optionValueModel->save();
        } else {
            $optionValueModel = ProductOptionValue::create([
                'name' => $optionValueName,
                'product_option_id' => $productOptionId,
            ]);
        }

        return $optionValueModel;
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

    protected function searchAndChangeValue(
        string|int $needle,
        array $haystack,
        int $newValue,
        string $field = 'option_id'
    ): array {
        return collect($haystack)->map(function ($variant) use ($needle, $newValue, $field) {
            /** @var array<int, \Domain\Product\DataTransferObjects\VariantCombinationData> $variantCombination */
            $variantCombination = $variant->combination;

            $newCombinations = collect($variantCombination)
                ->map(function ($combination) use ($needle, $newValue, $field) {
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
