<?php

declare(strict_types=1);

namespace Domain\Product\DataTransferObjects;

use Support\MetaData\DataTransferObjects\MetaDataData;
use Illuminate\Http\UploadedFile;

class ProductData
{
    public function __construct(
        public readonly string $name,
        public readonly MetaDataData $meta_data,
        public readonly string $sku,
        public readonly float $retail_price,
        public readonly float $selling_price,
        public readonly int $stock,
        public readonly int $minimum_order_quantity = 1,
        public readonly bool $status = true,
        public readonly bool $is_digital_product = false,
        public readonly bool $is_featured = false,
        public readonly bool $is_special_offer = false,
        public readonly bool $allow_customer_remarks = false,
        public readonly array $taxonomy_terms = [],
        public readonly ?float $weight = null,
        public ?array $product_options = [],
        public ?array $product_variants = [],
        public readonly ?float $length = null,
        public readonly ?float $width = null,
        public readonly ?float $height = null,
        public readonly ?string $description = null,
        public readonly UploadedFile|string|null|array $images = null,
        public readonly UploadedFile|string|null|array $videos = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            meta_data: MetaDataData::fromArray($data['meta_data']),
            taxonomy_terms: array_map(fn ($termData) => (int)$termData, $data['taxonomy_terms']),
            sku: $data['sku'],
            description: $data['description'],
            retail_price: $data['retail_price'],
            selling_price: $data['selling_price'],
            length: $data['length'],
            width: $data['width'],
            height: $data['height'],
            weight: $data['weight'],
            status: $data['status'],
            stock: $data['stock'],
            minimum_order_quantity: $data['minimum_order_quantity'],
            is_featured: $data['is_featured'],
            is_special_offer: $data['is_special_offer'],
            allow_customer_remarks: $data['allow_customer_remarks'],
            images: $data['images'],
            videos: $data['videos'],
            product_options: array_map(
                fn ($option) => (ProductOptionData::fromArray($option)),
                $data['product_options'][0] ?? []
            ),
            product_variants: array_map(fn ($variant) => (ProductVariantData::fromArray([
                ...$variant,
                'selling_price' => (float)$variant['selling_price'],
                'retail_price' => (float)$variant['retail_price']
            ])), $data['product_variants'] ?? []),
        );
    }
}
