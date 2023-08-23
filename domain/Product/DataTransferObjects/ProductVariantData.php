<?php

declare(strict_types=1);

namespace Domain\Product\DataTransferObjects;

class ProductVariantData
{
    public function __construct(
        public readonly int | string $id,
        public readonly string $sku,
        public readonly array $combination,
        public readonly float $retail_price,
        public readonly float $selling_price,
        public readonly ?bool $status = false,
        public readonly ?int $stock = null,
        public readonly ?int $product_id = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            product_id: $data['product_id'] ?? null,
            sku: $data['sku'],
            combination: array_map(fn ($item) => (VariantCombinationData::fromArray($item)), $data['combination']),
            retail_price: $data['retail_price'],
            selling_price: $data['selling_price'],
            stock: $data['stock'],
            status: $data['status'],
        );
    }

    public static function withCombination(array $combination, self $data): self
    {
        return new self(
            id: $data->id,
            product_id: $data->product_id ?? null,
            sku: $data->sku,
            combination: $combination,
            retail_price: $data->retail_price,
            selling_price: $data->selling_price,
            stock: $data->stock,
            status: $data->status,
        );
    }
}
