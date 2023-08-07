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
            /** If for update */
            if (!$isCreate) {
                foreach ($productData->product_variants as $productVariant) {
                    $variant = ProductVariant::where('combination', 'LIKE', '%"option_value_id": ' . $productVariant['combination'][0]['option_value_id'] . '%')
                        ->when(isset($productVariant['combination'][1]), function ($query) use ($productVariant) {
                            return $query->where('combination', 'LIKE', '%"option_value_id": ' . $productVariant['combination'][1]['option_value_id'] . '%');
                        })->first();

                    if ($variant) {
                        $variant->update($this->prepareVariantData($productVariant));
                    } else {
                        $this->createProductVariant($product->id, $productVariant);
                    }
                }
            } else {
                foreach ($productData->product_variants as $productVariant) {
                    $this->createProductVariant($product->id, $productVariant);
                }
            }
        }
    }

    private function createProductVariant(int $productId, array $productVariant): void
    {
        ProductVariant::create([
            'product_id' => $productId,
            'sku' => $productVariant['sku'],
            'combination' => $productVariant['combination'],
            'retail_price' => $productVariant['retail_price'],
            'selling_price' => $productVariant['selling_price'],
            'stock' => $productVariant['stock'],
            'status' => $productVariant['status'] ?? 1,
        ]);
    }

    private function prepareVariantData(array $productVariant): array
    {
        return [
            'sku' => $productVariant['sku'],
            'combination' => $productVariant['combination'],
            'retail_price' => $productVariant['retail_price'],
            'selling_price' => $productVariant['selling_price'],
            'stock' => $productVariant['stock'],
            'status' => $productVariant['status'],
        ];
    }
}
