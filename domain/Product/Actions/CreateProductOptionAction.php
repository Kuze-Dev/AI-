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
            foreach ($productData->product_options as $key => &$productOption) {
                $newProductOptionModel = ProductOption::create([
                    'product_id' => $product->id,
                    'name' => $productOption['name'],
                ]);

                $productData->product_variants = $this->searchAndChangeValue(
                    $productOption['id'],
                    $productData->product_variants,
                    $newProductOptionModel->id
                );
                $productData->product_options[$key]['id'] = $newProductOptionModel->id;

                foreach ($productOption['productOptionValues'] as $key2 => $productOptionValue) {
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
