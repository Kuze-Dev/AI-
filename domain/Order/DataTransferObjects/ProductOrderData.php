<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class ProductOrderData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $sku,
        public readonly string $description,
        public readonly string $retail_price,
        public readonly string $selling_price,
        public readonly int $stock,
        public readonly int $status,
        public readonly int $is_digital_product,
        public readonly int $is_featured,
        public readonly int $is_special_offer,
        public readonly int $allow_customer_remarks
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            slug: $data['slug'],
            sku: $data['sku'],
            description: $data['description'],
            retail_price: $data['retail_price'],
            selling_price: $data['selling_price'],
            stock: $data['stock'],
            status: $data['status'],
            is_digital_product: $data['is_digital_product'],
            is_featured: $data['is_featured'],
            is_special_offer: $data['is_special_offer'],
            allow_customer_remarks: $data['allow_customer_remarks'],
        );
    }
}
