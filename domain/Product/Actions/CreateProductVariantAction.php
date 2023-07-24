<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOption;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;

class CreateProductVariantAction
{
    public function execute(Product $product, ProductData $productData, bool $isCreate = true): void
    {
        if (filled($productData->product_variants)) {
            if (!$isCreate) {
                // Remove of Product Variants
                $mappedVariantIds = array_map(function ($item) {
                    return $item['id'];
                }, $productData->product_variants);

                if (count($mappedVariantIds)) {
                    $toRemoveProductVariants = ProductVariant::where('product_id', $product->id)
                        ->whereNotIn('id', $mappedVariantIds)->get();

                    foreach ($toRemoveProductVariants as $productVariant) {
                        $productVariant->delete();
                    }
                }
            }

            foreach ($productData->product_variants as $productVariant) {
                if ($isCreate) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $productVariant['sku'],
                        'combination' => $productVariant['combination'],
                        'retail_price' => $productVariant['retail_price'],
                        'selling_price' => $productVariant['selling_price'],
                        'stock' => $productVariant['stock'],
                        'status' => $productVariant['status'] ?? 1,
                    ]);
                } else {
                    $productVariantModel = ProductVariant::find($productVariant['id']);
                    if ($productVariantModel) {
                        $productVariantModel->product_id = $product['id'];
                        $productVariantModel->sku = $productVariant['sku'];
                        $productVariantModel->combination = $productVariant['combination'];
                        $productVariantModel->retail_price = $productVariant['retail_price'];
                        $productVariantModel->selling_price = $productVariant['selling_price'];
                        $productVariantModel->stock = $productVariant['stock'];
                        $productVariantModel->status = $productVariant['status'];
                        $productVariantModel->save();
                    }
                }
            }
        }
    }
}
