<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;

class CreateProductVariantAction
{
    public function execute(Product $product, ProductData $productData): void
    {
        if (filled($productData->product_variants)) {
            foreach ($productData->product_variants as $productVariant) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $productVariant['sku'],
                    'combination' => $productVariant['combination'],
                    'retail_price' => $productVariant['retail_price'],
                    'selling_price' => $productVariant['selling_price'],
                    'stock' => $productVariant['stock'],
                    'status' => $productVariant['status'] ?? 1,
                ]);
            }
        }

    }
}
