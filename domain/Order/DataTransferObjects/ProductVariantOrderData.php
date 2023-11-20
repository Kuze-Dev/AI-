<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

use Domain\Cart\Helpers\PrivateCart\ComputedTierSellingPrice;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;

class ProductVariantOrderData
{
    public function __construct(
        public readonly string $sku,
        public readonly array $combination,
        public readonly float|string $retail_price,
        public readonly float|string $selling_price,
        public readonly bool $status,
        public readonly ProductOrderData $product,
        public readonly ?int $stock,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $combinations = [];
        foreach ($data['combination'] as $combinationData) {
            $combinations[] = new ProductVariantCombinationData(
                option_id: $combinationData['option_id'],
                option: $combinationData['option'],
                option_value_id: 1,
                option_value: $combinationData['option_value'],
                option_value_data: []
            );
        }

        return new self(
            sku: $data['sku'],
            combination: $combinations,
            retail_price: number_format((float) $data['retail_price'], 2, '.', ','),
            selling_price: number_format((float) $data['selling_price'], 2, '.', ','),
            status: $data['status'],
            product: ProductOrderData::fromArray($data['product']),
            stock: $data['stock'] ?? null,
        );
    }

    public static function fromProductVariant(ProductVariant $productVariant): self
    {
        $combinations = [];
        foreach ($productVariant->combination as $combinationData) {
            $productOptionValue = ProductOptionValue::with('media')
                ->where('id', $combinationData['option_value_id'])->first();

            $combinations[] = new ProductVariantCombinationData(
                option_id: $combinationData['option_id'],
                option: $combinationData['option'],
                option_value_id: $productOptionValue->id,
                option_value: $combinationData['option_value'],
                option_value_data: $productOptionValue->data
            );
        }

        /** @var \Domain\Product\Models\Product $product */
        $product = $productVariant->product;

        //product tiering discount
        $selling_price = $productVariant->selling_price;
        if ($product->relationLoaded('productTier') && $product->productTier->isNotEmpty()) {
            $selling_price = app(ComputedTierSellingPrice::class)->execute($product, (float) $selling_price);
        }

        return new self(
            sku: $productVariant->sku,
            combination: $combinations,
            retail_price: number_format((float) $productVariant->retail_price, 2, '.', ','),
            selling_price: number_format((float) $selling_price, 2, '.', ','),
            status: (bool) $productVariant->status,
            product: ProductOrderData::fromProduct($product),
            stock: $productVariant->stock ?? null,
        );
    }
}
