<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;

class UpdateProductOptionAction
{
    public function execute(Product $product, ProductData $productData): void
    {
        if (filled($productData->product_options) && filled($productData->product_options[0])) {
            foreach ($productData->product_options[0] as $key => &$productOption) {
                $productOptionModel = ProductOption::find($productOption['id']);

                if ($productOptionModel) {
                    $productOptionModel->product_id = $product->id;
                    $productOptionModel->name = $productOption['name'];
                    $productOptionModel->save();
                } else {
                    $newProductOptionModel = ProductOption::create([
                        'product_id' => $product->id,
                        'name' => $productOption['name'],
                    ]);

                    $productData->product_variants = $this->searchAndChangeValue(
                        $productOption['id'],
                        $productData->product_variants,
                        $newProductOptionModel->id
                    );
                    $productData->product_options[0][$key]['id'] = $newProductOptionModel->id;
                }

                foreach ($productOption['productOptionValues'] as $key2 => $productOptionValue) {
                    $optionValueModel = ProductOptionValue::find($productOptionValue['id']);

                    if ($optionValueModel) {
                        $optionValueModel->name = $productOptionValue['name'];
                        $optionValueModel->product_option_id = $productOption['id'];
                        $optionValueModel->save();
                    } else {
                        $newOptionValueModel = ProductOptionValue::create([
                            'name' => $productOptionValue['name'],
                            'product_option_id' => $productOption['id'],
                        ]);

                        $productOption['productOptionValues'][$key2]['id'] = $newOptionValueModel->id;
                        $productData->product_variants = $this->searchAndChangeValue(
                            $productOptionValue['id'],
                            $productData->product_variants,
                            $newOptionValueModel->id,
                            'option_value_id'
                        );
                    }
                }

                /** Removal of Option Values */
                $mappedOptionValueIds = array_map(function ($item) {
                    return $item['id'];
                }, $productOption['productOptionValues']);
                if (count($mappedOptionValueIds)) {
                    $toRemoveOptionValues = ProductOptionValue::where(
                        'product_option_id',
                        $productOption['id']
                    )->whereNotIn('id', $mappedOptionValueIds)->get();
                    foreach ($toRemoveOptionValues as $optionValue) {
                        $optionValue->delete();
                    }
                }
            }

            /** Removal of product options */
            $mappedOptionIds = array_map(function ($item) {
                return $item['id'];
            }, $productData->product_options[0]);

            if (count($mappedOptionIds)) {
                $toRemoveOptions = ProductOption::where(
                    'product_id',
                    $product->id
                )->whereNotIn('id', $mappedOptionIds)->get();
                foreach ($toRemoveOptions as $option) {
                    $option->delete();
                }
            }
        } else {
            ProductOption::whereProductId($product->id)->delete();
        }
    }

    protected function searchAndChangeValue($needle, &$haystack, $newValue, $field = 'option_id')
    {
        foreach ($haystack as $key => $variant) {
            foreach ($variant['combination'] as $key2 => $combination) {
                if ($combination[$field] == $needle) {
                    $haystack[$key]['combination'][$key2][$field] = $newValue;
                }
            }
        }

        return $haystack;
    }
}
