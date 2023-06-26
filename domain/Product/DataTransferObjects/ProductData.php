<?php

declare(strict_types=1);

namespace Domain\Product\DataTransferObjects;

use Domain\Support\MetaData\DataTransferObjects\MetaDataData;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Illuminate\Http\UploadedFile;

class ProductData
{
    public function __construct(
        public readonly string $name,
        // public readonly RouteUrlData $route_url_data,
        public readonly MetaDataData $meta_data,
        public readonly string $sku,
        public readonly ?string $description = null,
        public readonly float $retail_price,
        public readonly float $selling_price,
        public readonly ?float $shipping_fee = null,
        public readonly bool $status = true,
        public readonly int $stock,
        public readonly ?array $taxonomy_terms = null,
        public readonly bool $is_digital_product = false,
        public readonly bool $is_featured = false,
        public readonly bool $is_special_offer = false,
        public readonly bool $allow_customer_remarks = false,
        public readonly bool $allow_remark_with_image = false,
        public readonly UploadedFile|string|null $image = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            // route_url_data: RouteUrlData::fromArray($data['route_url'] ?? []),
            meta_data: MetaDataData::fromArray($data['meta_data']),
            taxonomy_terms: $data['taxonomy_terms'] ?? [],
            sku: $data['sku'],
            description: $data['description'],
            retail_price: $data['retail_price'],
            selling_price: $data['selling_price'],
            shipping_fee: $data['shipping_fee'],
            status: $data['status'],
            stock: $data['stock'],
            // is_digital_product: $data['is_digital_product'],
            is_featured: $data['is_featured'],
            is_special_offer: $data['is_special_offer'],
            allow_customer_remarks: $data['allow_customer_remarks'],
            allow_remark_with_image: $data['allow_remark_with_image'],
            image: $data['image'],
        );
    }
}
