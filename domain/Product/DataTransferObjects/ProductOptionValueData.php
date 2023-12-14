<?php

declare(strict_types=1);

namespace Domain\Product\DataTransferObjects;

use Illuminate\Http\UploadedFile;

class ProductOptionValueData
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly int|string $product_option_id,
        public readonly ?string $icon_value = null,
        public readonly ?string $icon_type = 'text',
        // public readonly UploadedFile|string|null|array $images = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            slug: $data['slug'],
            icon_type: $data['icon_type'] ?? 'text',
            icon_value: $data['icon_value'] ?? '',
            // images: $data['images'] ?? [],
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
            icon_type: $data->icon_type,
            icon_value: $data->icon_value,
            // images: $data->images,
        );
    }

    public static function withOptionId(int $optionId, self $data): self
    {
        return new self(
            id: $data->id,
            name: $data->name,
            slug: $data->slug,
            product_option_id: $optionId,
            icon_type: $data->icon_type,
            icon_value: $data->icon_value,
            // images: $data->images,
        );
    }
}
