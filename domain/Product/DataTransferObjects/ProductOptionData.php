<?php

declare(strict_types=1);

namespace Domain\Product\DataTransferObjects;

class ProductOptionData
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $name,
        public readonly string $slug,
        public array $productOptionValues,
        public bool $is_custom = false,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            slug: $data['slug'],
            productOptionValues: array_map(
                fn ($optionValue) => (ProductOptionValueData::fromArray($optionValue)),
                $data['productOptionValues']
            ),
            is_custom: $data['is_custom'],
        );
    }

    public static function withId(int $id, self $data): self
    {
        return new self(
            id: $id,
            name: $data->name,
            slug: $data->slug,
            productOptionValues: $data->productOptionValues,
            is_custom: $data->is_custom,
        );
    }

    public static function withProductOptionValues(array $productOptionValues, self $data): self
    {
        return new self(
            id: $data->id,
            name: $data->name,
            slug: $data->slug,
            productOptionValues: $productOptionValues,
            is_custom: $data->is_custom,
        );
    }
}
