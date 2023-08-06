<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

use Domain\Product\Models\ProductVariant;

class ProductVariantOrderData
{
    public function __construct(
        public readonly string $sku,
        public readonly array $combination,
        public readonly float|string $retail_price,
        public readonly float|string $selling_price,
        public readonly int $stock,
        public readonly string $status,
        public readonly ProductOrderData $product
    ) {
    }

    public static function fromArray(array $data): self
    {
        $combinations = [];
        foreach ($data['combination'] as $combinationData) {
            $combinations[] = new ProductVariantCombinationData(
                option: $combinationData['option'],
                option_value: $combinationData['option_value']
            );
        }

        return new self(
            sku: $data['sku'],
            combination: $combinations,
            retail_price: $data['retail_price'],
            selling_price: $data['selling_price'],
            stock: $data['stock'],
            status: $data['status'],
            product: ProductOrderData::fromArray($data['product']),
        );
    }

    public static function fromProductVariant(ProductVariant $productVariant): self
    {
        $combinations = [];
        foreach ($productVariant->combination as $combinationData) {
            $combinations[] = new ProductVariantCombinationData(
                option: $combinationData['option'],
                option_value: $combinationData['option_value']
            );
        }

        /** @var \Domain\Product\Models\Product $product */
        $product = $productVariant->product;

        return new self(
            sku: $productVariant->sku,
            combination: $combinations,
            retail_price: $productVariant->retail_price,
            selling_price: $productVariant->selling_price,
            stock: $productVariant->stock,
            status: $productVariant->status,
            product: ProductOrderData::fromProduct($product),
        );
    }
}
