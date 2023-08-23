<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;

class CreateProductOptionAction
{
    public function execute(Product $product, ProductData $productData): void
    {
        if (filled($productData->product_options)) {
            foreach ($productData->product_options as $key => $productOption) {
                $newProductOptionModel = ProductOption::create([
                    'product_id' => $product->id,
                    'name' => $productOption->name,
                ]);

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

                foreach ($productOption->productOptionValues as $key2 => $productOptionValue) {
                    $productOptionValue = $productOptionValue->withOptionId($productOption->id, $productOptionValue);
                    $proxyOptionValueId = $productOptionValue->id;

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

                    $productOption->productOptionValues[$key2] = $productOptionValue;
                }

                $productData->product_options[$key] = $productOption;
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
