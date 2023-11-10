<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductVariantData;
use Domain\Product\Models\ProductVariant;

class UpdateProductVariantFromCsvAction
{
    public function execute(ProductVariant $productVariant, ProductVariantData $productVariantData): ProductVariant
    {
        $productVariant->update($this->getProductAttributes($productVariantData));

        return $productVariant;
    }

    protected function getProductAttributes(ProductVariantData $productVariantData): array
    {
        return array_filter(
            [
                'sku' => $productVariantData->sku,
                'combination' => $productVariantData->combination,
                'retail_price' => $productVariantData->retail_price,
                'selling_price' => $productVariantData->selling_price,
                'status' => $productVariantData->status,
                'stock' => $productVariantData->stock,
                'product_id' => $productVariantData->product_id,
            ],
            fn ($value) => filled($value)
        );
    }
}
