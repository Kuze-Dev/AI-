<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

use Domain\Product\Models\Product;

class ProductOrderData
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $slug,
        public readonly string $sku,
        public readonly float|string $retail_price,
        public readonly float|string $selling_price,
        public readonly bool $status,
        public readonly bool $is_digital_product,
        public readonly bool $is_featured,
        public readonly bool $is_special_offer,
        public readonly bool $allow_customer_remarks,
        public readonly ?string $description,
        public readonly ?int $stock,
        public readonly ?bool $allow_stocks,
        public readonly ?int $minimum_order_quantity,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            slug: $data['slug'],
            sku: $data['sku'],
            retail_price: number_format((float) $data['retail_price'], 2, '.', ','),
            selling_price: number_format((float) $data['selling_price'], 2, '.', ','),
            status: $data['status'],
            is_digital_product: $data['is_digital_product'],
            is_featured: $data['is_featured'],
            is_special_offer: $data['is_special_offer'],
            allow_customer_remarks: $data['allow_customer_remarks'],
            description: $data['description'] ?? null,
            stock: $data['stock'] ?? null,
            allow_stocks: isset($data['allow_stocks']) ? $data['allow_stocks'] : null,
            minimum_order_quantity: isset($data['minimum_order_quantity']) ? $data['minimum_order_quantity'] : null,
        );
    }

    public static function fromProduct(Product $product): self
    {
        return new self(
            id: $product->id,
            name: $product->name,
            slug: $product->slug,
            sku: $product->sku,
            retail_price: number_format((float) $product->retail_price, 2, '.', ','),
            selling_price: number_format((float) $product->selling_price, 2, '.', ','),
            status: (bool) $product->status,
            is_digital_product: (bool) $product->is_digital_product,
            is_featured: (bool) $product->is_featured,
            is_special_offer: (bool) $product->is_special_offer,
            allow_customer_remarks: (bool) $product->allow_customer_remarks,
            description: $product->description ?? null,
            stock: $product->stock ?? null,
            allow_stocks: isset($product->allow_stocks) ? $product->allow_stocks : null,
            minimum_order_quantity: isset($product->minimum_order_quantity) ? $product->minimum_order_quantity : null,
        );
    }
}
