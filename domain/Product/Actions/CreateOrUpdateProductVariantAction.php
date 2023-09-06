<?php

declare(strict_types=1);

namespace Domain\Product\Actions;

use Domain\Product\DataTransferObjects\ProductVariantData;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductVariant;

class CreateOrUpdateProductVariantAction
{
    public function execute(Product $product, array $productVariants, bool $isCreate = true): void
    {
        /** If met, flush product variants */
        if ( ! filled($productVariants)) {
            ProductVariant::whereProductId($product->id)->delete();

            return;
        }

        /** If for variant creation */
        if ($isCreate) {
            foreach ($productVariants as $productVariant) {
                $this->createProductVariant($product->id, $productVariant);
            }

            return;
        }

        $this->sanitizeVariants($product->id, $productVariants);

        $this->createOrUpdateProductVariants($product->id, $productVariants);
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

        $variants->each(function ($variant) use ($productVariants) {
            $found = collect($productVariants)->first(function ($value) use ($variant) {
                /** @var array<int, \Domain\Product\DataTransferObjects\VariantCombinationData> $valueCombination */
                $valueCombination = $value->combination;

                return count($variant->combination) === count($valueCombination) &&
                    collect($variant->combination)
                        ->pluck('option_value_id')
                        ->diff(
                            collect($valueCombination)->pluck('option_value_id')
                        )->isEmpty();
            });

            if ( ! $found) {
                $variant->delete();
            }
        });
    }

    protected function createOrUpdateProductVariants(int $productId, array $productVariants): void
    {
        foreach ($productVariants as $productVariant) {
            $variant = ProductVariant::where(
                'combination',
                'LIKE',
                '%"option_value_id": ' . $productVariant->combination[0]->option_value_id . '%'
            )
                ->when(isset($productVariant->combination[1]), function ($query) use ($productVariant) {
                    return $query->where(
                        'combination',
                        'LIKE',
                        '%"option_value_id": ' . $productVariant->combination[1]->option_value_id . '%'
                    );
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
