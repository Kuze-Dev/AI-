<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\DataTransferObjects\ProductOptionData;
use Domain\Product\DataTransferObjects\ProductOptionValueData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;

class CreateProductOptionAction
{
    public function execute(Product $product, ProductData $productData): array
    {
        $mutableVariants = $productData->product_variants ?? [];
        $mutableOptions = $productData->product_options;

        if (filled($mutableOptions)) {
            // Create product options & update mutable variants for variant insertion
            collect($mutableOptions)->map(function (ProductOptionData $productOption) use ($product, $mutableVariants) {
                $optionModel = $this->createOption($product, $productOption);

                $mutableVariants = $this->searchAndChangeValue($productOption->id, $mutableVariants, $optionModel->id);

                $productOption = $productOption->withId($optionModel->id, $productOption);

                $mutableOptionValues = $productOption->productOptionValues;

                // Create product option values
                $collectedOptionValues = collect($mutableOptionValues)
                    ->map(function (ProductOptionValueData $optionValue) use ($productOption, $mutableVariants) {
                        $productOptionId = $productOption->id;

                        /** @var int $productOptionId */
                        $optionValue = $optionValue->withOptionId($productOptionId, $optionValue);

                        $proxyOptionValueId = $optionValue->id;

                        $optionValueModel = $this->createOptionValue($productOption, $optionValue);

                        $optionValue = $optionValue->withId($optionValueModel->id, $optionValue);

                        $mutableVariants = $this->searchAndChangeValue(
                            $proxyOptionValueId,
                            $mutableVariants,
                            $optionValueModel->id,
                            'option_value_id'
                        );

                        return $optionValue;
                    })->toArray();

                $productOption = $productOption->withProductOptionValues($collectedOptionValues, $productOption);

                return $productOption;
            });
        }

        return $mutableVariants;
    }

    protected function createOption(Product $product, ProductOptionData $productOption): ProductOption
    {
        return ProductOption::create([
            'product_id' => $product->id,
            'name' => $productOption->name,
        ]);
    }

    protected function createOptionValue(ProductOptionData $productOption, ProductOptionValueData $optionValue): ProductOptionValue
    {
        return ProductOptionValue::create([
            'name' => $optionValue->name,
            'product_option_id' => $productOption->id,
        ]);
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
