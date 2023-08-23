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
    public function execute(Product $product, ProductData $productData): void
    {
        /** Flush Product Option */
        if (!filled($productData->product_options) && !filled($productData->product_variants)) {
            ProductOption::whereProductId($product->id)->delete();
            return;
        }

        /** Process Update or Create of Product Options and Option Values */
        foreach ($productData->product_options as $key => $productOption) {
            $productOptionModel = ProductOption::find($productOption->id);

            if ($productOptionModel) {
                $productOptionModel->product_id = $product->id;
                $productOptionModel->name = $productOption->name;
                $productOptionModel->save();
            } else {
                $newProductOptionModel = ProductOption::create([
                    'product_id' => $product->id,
                    'name' => $productOption->name,
                ]);

                // Update product variant
                $productData->product_variants = $this->searchAndChangeValue(
                    $productOption->id,
                    $productData->product_variants,
                    $newProductOptionModel->id
                );

                $productOption = $productOption
                    ->withId(
                        $newProductOptionModel->id,
                        $productOption
                    );
            }


            // Process Update or Create of Product Option Value             
            foreach ($productOption->productOptionValues as $key2 => $productOptionValue) {
                $productOptionValue = $productOptionValue->withOptionId($productOption->id, $productOptionValue);
                $proxyOptionValueId = $productOptionValue->id;

                $optionValueModel = ProductOptionValue::find($productOptionValue->id);

                if ($optionValueModel) {
                    $optionValueModel->name = $productOptionValue->name;
                    $optionValueModel->product_option_id = $productOption->id;
                    $optionValueModel->save();
                } else {
                    $newOptionValueModel = ProductOptionValue::create([
                        'name' => $productOptionValue->name,
                        'product_option_id' => $productOption->id,
                    ]);

                    $productOptionValue = $productOptionValue
                        ->withId($newOptionValueModel->id, $productOptionValue);

                    $productData->product_variants = $this->searchAndChangeValue(
                        $proxyOptionValueId,
                        $productData->product_variants,
                        $newOptionValueModel->id,
                        'option_value_id'
                    );
                }

                $productOption->productOptionValues[$key2] = $productOptionValue;
            }

            $productData->product_options[$key] = $productOption;

            $this->sanitizeOptionValues($productOption);
        }

        $this->sanitizeOptions($productData, $product->id);
    }

    protected function sanitizeOptions(ProductData $productData, int $productId): void
    {
        // Removal of Product Options
        $mappedOptionIds = array_map(function ($item) {
            return $item->id;
        }, $productData->product_options);

        if (count($mappedOptionIds)) {
            $toRemoveOptions = ProductOption::where(
                'product_id',
                $productId
            )->whereNotIn('id', $mappedOptionIds)->get();
            foreach ($toRemoveOptions as $option) {
                $option->delete();
            }
        }
    }

    protected function sanitizeOptionValues(ProductOptionData $productOption): void
    {
        // Removal of Product Option Values
        $mappedOptionValueIds = array_map(function ($item) {
            return $item->id;
        }, $productOption->productOptionValues);

        if (count($mappedOptionValueIds)) {
            $toRemoveOptionValues = ProductOptionValue::where(
                'product_option_id',
                $productOption->id
            )->whereNotIn('id', $mappedOptionValueIds)->get();

            foreach ($toRemoveOptionValues as $optionValue) {
                $optionValue->delete();
            }
        }
    }

    protected function searchAndChangeValue($needle, $haystack, $newValue, $field = 'option_id')
    {
        $newCombinations = [];
        $newVariants = [];
        foreach ($haystack as $key => $variant) {
            foreach ($variant->combination as $key2 => $combination) {
                $variantCombination = $haystack[$key]->combination[$key2];

                if ($combination->{$field} == $needle) {
                    if ($field == "option_id") {
                        array_push($newCombinations, $variantCombination->withOptionId($newValue, $variantCombination));
                    }

                    if ($field == "option_value_id") {
                        array_push($newCombinations, $variantCombination->withOptionValueId($newValue, $variantCombination));
                    }
                } else {
                    array_push($newCombinations, $variantCombination);
                }
            }

            array_push($newVariants, $variant->withCombination($newCombinations, $variant));
            $newCombinations = [];
        }

        return $newVariants;
    }
}
