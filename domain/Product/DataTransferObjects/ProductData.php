<?php

declare(strict_types=1);

namespace Domain\Product\DataTransferObjects;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Support\MetaData\DataTransferObjects\MetaDataData;

class ProductData
{
    public function __construct(
        public readonly string $name,
        public readonly MetaDataData $meta_data,
        public readonly string $sku,
        public readonly float $retail_price,
        public readonly float $selling_price,
        public readonly int $minimum_order_quantity = 1,
        public readonly bool $status = true,
        public readonly bool $is_digital_product = false,
        public readonly bool $is_featured = false,
        public readonly bool $is_special_offer = false,
        public readonly bool $allow_customer_remarks = false,
        public readonly bool $allow_stocks = true,
        public readonly array $taxonomy_terms = [],
        public readonly bool $allow_guest_purchase = false,
        public readonly bool $skip_media_sync = false,
        public readonly ?float $weight = null,
        public ?array $product_options = [],
        public ?array $product_variants = [],
        public readonly ?int $stock = null,
        public readonly ?float $length = null,
        public readonly ?float $width = null,
        public readonly ?float $height = null,
        public readonly ?string $description = null,
        public readonly UploadedFile|string|null|array $images = null,
        public readonly UploadedFile|string|null|array $videos = null,
        public readonly array $media_collection = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            meta_data: MetaDataData::fromArray($data['meta_data']),
            sku: $data['sku'],
            retail_price: $data['retail_price'],
            selling_price: $data['selling_price'],
            minimum_order_quantity: $data['minimum_order_quantity'],
            status: $data['status'],
            is_featured: $data['is_featured'],
            is_special_offer: $data['is_special_offer'],
            allow_customer_remarks: $data['allow_customer_remarks'],
            allow_stocks: $data['allow_stocks'],
            taxonomy_terms: array_map(fn ($termData) => (int) $termData, $data['taxonomy_terms'] ?? []),
            allow_guest_purchase: $data['allow_guest_purchase'] ?? false,
            skip_media_sync: $data['skip_media_sync'] ?? false,
            weight: $data['weight'],
            product_options: array_map(
                fn ($option) => (ProductOptionData::fromArray($option)),
                $data['product_options'][0] ?? []
            ),
            product_variants: array_map(fn ($variant) => (ProductVariantData::fromArray([
                ...$variant,
                'selling_price' => (float) $variant['selling_price'],
                'retail_price' => (float) $variant['retail_price'],
            ])), $data['product_variants'] ?? []),
            stock: $data['stock'] ?? null,
            length: $data['length'],
            width: $data['width'],
            height: $data['height'],
            description: $data['description'],
            images: $data['images'],
            videos: $data['videos'],
            media_collection: [
                ['collection' => 'image', 'materials' => $data['images']],
                ['collection' => 'video', 'materials' => Arr::wrap($data['videos'])],
            ],
        );
    }

    public static function fromCsv(array $data): self
    {
        return new self(
            name: $data['name'],
            meta_data: MetaDataData::fromArray($data['meta_data']),
            taxonomy_terms: array_map(fn ($termData) => (int) $termData, $data['taxonomy_terms'] ?? []),
            sku: $data['sku'],
            retail_price: $data['retail_price'],
            selling_price: $data['selling_price'],
            length: $data['length'],
            width: $data['width'],
            height: $data['height'],
            weight: $data['weight'],
            stock: $data['stock'] ?? null,
            images: $data['images'],
            media_collection: [
                ['collection' => 'image', 'materials' => $data['images']],
            ],
            allow_guest_purchase: $data['allow_guest_purchase'] ?? false,
            description: $data['description'] ?? '',
            product_options: array_map(
                fn ($option) => (ProductOptionData::fromArray($option)),
                $data['product_options'] ?? []
            ),
            product_variants: array_map(fn ($variant) => (ProductVariantData::fromArray([
                ...$variant,
                'selling_price' => (float) $variant['selling_price'],
                'retail_price' => (float) $variant['retail_price'],
            ])), $data['product_variants'] ?? []),
            skip_media_sync: $data['skip_media_sync'] ?? false,
        );
    }

    public static function fromCsvBulkUpdate(array $data): self
    {
        return new self(
            name: $data['name'],
            meta_data: MetaDataData::fromArray($data['meta_data']),
            sku: $data['sku'],
            status: $data['status'] ?? true,
            retail_price: $data['retail_price'],
            selling_price: $data['selling_price'],
            length: $data['length'],
            width: $data['width'],
            height: $data['height'],
            weight: $data['weight'],
            stock: $data['stock'] ?? null,
            product_options: array_map(
                fn ($option) => (ProductOptionData::fromArray($option)),
                $data['product_options'] ?? []
            ),
            product_variants: array_map(fn ($variant) => (ProductVariantData::fromArray([
                ...$variant,
                'selling_price' => (float) $variant['selling_price'],
                'retail_price' => (float) $variant['retail_price'],
            ])), $data['product_variants'] ?? []),
            skip_media_sync: $data['skip_media_sync'] ?? false,
        );
    }
}
