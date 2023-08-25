<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductData;
use Domain\Product\DataTransferObjects\ProductVariantData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;
use Illuminate\Support\Arr;

class CreateOrUpdateProductVariantAction
{
    public function execute(Product $product, ProductData $productData, bool $isCreate = true): void
    {
        /** If met, flush product variants */
        if ( ! filled($productData->product_variants)) {
            ProductVariant::whereProductId($product->id)->delete();

            return;
        }

        /** If for variant creation */
        if ($isCreate) {
            foreach ($productData->product_variants ?? [] as $productVariant) {
                $this->createProductVariant($product->id, $productVariant);
            }

            return;
        }

        $this->sanitizeVariants($product->id, $productData->product_variants ?? []);

        $this->createOrUpdateProductVariants($product->id, $productData->product_variants ?? []);
    }

    protected function createProductVariant(int $productId, ProductVariantData $productVariant): void
    {
        ProductVariant::create(
            array_merge(['product_id' => $productId], $this->prepareVariantData($productVariant))
        );
    }

    protected function sanitizeVariants(int $productId, array $productVariants): void
    {
        $variants = ProductVariant::where('product_id', $productId)->get();
        foreach ($variants as $variant) {
            $existingItem = Arr::where($productVariants, function (ProductVariantData $value) use ($variant) {
                if (count($variant->combination) !== count($value->combination)) {
                    return false;
                }

                if ($variant->combination[0]['option_value_id'] === $value->combination[0]->option_value_id) {
                    return (isset($variant->combination[1])
                        ? ($variant->combination[1]['option_value_id'] === $value->combination[1]->option_value_id)
                        : true);
                }

                return false;
            });

            if ( ! $existingItem) {
                $variant->delete();
            }
        }
    }

    protected function createOrUpdateProductVariants(int $productId, array $productVariants): void
    {
        foreach ($productVariants as $productVariant) {
            $variant = ProductVariant::where('combination', 'LIKE', '%"option_value_id": ' . $productVariant->combination[0]->option_value_id . '%')
                ->when(isset($productVariant->combination[1]), function ($query) use ($productVariant) {
                    return $query->where('combination', 'LIKE', '%"option_value_id": ' . $productVariant->combination[1]->option_value_id . '%');
                })
                ->where('product_id', $productId)
                ->first();

            $variant
                ? $variant->update($this->prepareVariantData($productVariant))
                : $this->createProductVariant($productId, $productVariant);
        }
    }

    protected function prepareVariantData(ProductVariantData $productVariant): array
    {
        return [
            'sku' => $productVariant->sku,
            'combination' => $productVariant->combination,
            'retail_price' => $productVariant->retail_price,
            'selling_price' => $productVariant->selling_price,
            'stock' => $productVariant->stock,
            'status' => $productVariant->status,
        ];
    }
}
