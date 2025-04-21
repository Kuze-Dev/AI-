<?php

declare(strict_types=1);

namespace Domain\Product\DataTransferObjects;

readonly class ProductVariantData
{
    public function __construct(
        public int|string $id,
        public string $sku,
        public array $combination,
        public float $retail_price,
        public float $selling_price,
        public ?bool $status = false,
        public ?int $stock = null,
        public ?int $product_id = null,
    ) {}

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
