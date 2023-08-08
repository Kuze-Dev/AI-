<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Support\Arr;

class CreateOrUpdateProductVariantAction
{
    public function execute(Product $product, ProductData $productData, bool $isCreate = true): void
    {
        if (filled($productData->product_variants)) {
            /** If for update */
            if (!$isCreate) {
                /** Removal of unnecessary product variants */
                $productVariants = ProductVariant::where('product_id', $product->id);
                $variants = $productVariants->get();
                foreach ($variants as $variant) {
                    $existingItem = Arr::where($productData->product_variants, function (array $value, int $key) use ($variant) {
                        if (count($variant->combination) !== count($value['combination'])) {
                            return false;
                        }

                        if ($variant->combination[0]['option_value_id'] === $value['combination'][0]['option_value_id']) {
                            if (isset($variant->combination[1])) {
                                if ($variant->combination[1]['option_value_id'] === $value['combination'][1]['option_value_id']) {
                                    return true;
                                } else {
                                    return false;
                                }
                            } else {
                                return true;
                            }
                        }
                        return false;
                    });

                    if (!$existingItem) {
                        $variant->delete();
                    }
                }

                foreach ($productData->product_variants as $productVariant) {
                    $variant = ProductVariant::where('combination', 'LIKE', '%"option_value_id": ' . $productVariant['combination'][0]['option_value_id'] . '%')
                        ->when(isset($productVariant['combination'][1]), function ($query) use ($productVariant) {
                            return $query->where('combination', 'LIKE', '%"option_value_id": ' . $productVariant['combination'][1]['option_value_id'] . '%');
                        })
                        ->where('product_id', $product->id)
                        ->first();

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
        ProductVariant::create(
            array_merge(['product_id' => $productId], $this->prepareVariantData($productVariant))
        );
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
