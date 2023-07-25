<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;

class CreateOrUpdateProductVariantAction
{
    public function execute(Product $product, ProductData $productData, bool $isCreate = true): void
    {
        if (filled($productData->product_variants)) {
            if (!$isCreate) {
                $existingProductVariants = ProductVariant::where('product_id', $product->id)->get();
                foreach ($existingProductVariants as $productVariant) {
                    $productVariant->delete();
                }
            }

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
