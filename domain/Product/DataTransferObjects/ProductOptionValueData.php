<?php

declare(strict_types=1);

namespace Domain\Product\DataTransferObjects;

class ProductOptionValueData
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly int|string $product_option_id,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            slug: $data['slug'],
            product_option_id: $data['product_option_id'],
        );
    }

    public static function withId(int $id, self $data): self
    {
        return new self(
            id: $id,
            name: $data->name,
            slug: $data->slug,
            product_option_id: $data->product_option_id,
        );
    }

    public static function withOptionId(int $optionId, self $data): self
    {
        return new self(
            id: $data->id,
            name: $data->name,
            slug: $data->slug,
            product_option_id: $optionId,
        );
    }
}
