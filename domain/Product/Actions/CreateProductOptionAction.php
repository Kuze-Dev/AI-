<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
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
            foreach ($mutableOptions ?? [] as $key => $productOption) {
                $optionModel = ProductOption::create([
                    'product_id' => $product->id,
                    'name' => $productOption->name,
                ]);

                $mutableVariants = $this->searchAndChangeValue(
                    $productOption->id,
                    $mutableVariants,
                    $optionModel->id
                );

                $productOption = $productOption->withId($optionModel->id, $productOption);
                $mutableOptionValues = $productOption->productOptionValues;

                foreach ($mutableOptionValues as $key2 => $optionValue) {
                    $optionValue = $optionValue->withOptionId($productOption->id, $optionValue);

                    $proxyOptionValueId = $optionValue->id;

                    $optionValueModel = ProductOptionValue::create([
                        'name' => $optionValue->name,
                        'product_option_id' => $productOption->id,
                    ]);

                    $optionValue = $optionValue->withId($optionValueModel->id, $optionValue);

                    $mutableVariants = $this->searchAndChangeValue(
                        $proxyOptionValueId,
                        $mutableVariants,
                        $optionValueModel->id,
                        'option_value_id'
                    );

                    $mutableOptionValues[$key2] = $optionValue;
                }

                $productOption = $productOption->withProductOptionValues($mutableOptionValues, $productOption);

                $mutableOptions[$key] = $productOption;
            }
        }

        return $mutableVariants;
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
