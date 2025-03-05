<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

use Domain\Cart\Helpers\PrivateCart\ComputedTierSellingPrice;
use Domain\Product\Models\Product;

readonly class ProductOrderData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $slug,
        public string $sku,
        public float|string $retail_price,
        public float|string $selling_price,
        public bool $status,
        public bool $is_digital_product,
        public bool $is_featured,
        public bool $is_special_offer,
        public bool $allow_customer_remarks,
        public ?string $description,
        public ?int $stock,
        public ?bool $allow_stocks,
        public ?int $minimum_order_quantity,
        public ?bool $allow_guest_purchase,
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
            allow_stocks: $data['allow_stocks'] ?? null,
            minimum_order_quantity: $data['minimum_order_quantity'] ?? null,
            allow_guest_purchase: $data['allow_guest_purchase'] ?? null,
        );
    }

    public static function fromProduct(Product $product): self
    {
        //product tiering discount
        $selling_price = $product->selling_price;

        if ($product->relationLoaded('productTier') && $product->productTier->isNotEmpty()) {
            $selling_price = app(ComputedTierSellingPrice::class)->execute($product, (float) $selling_price);
        }

        return new self(
            id: $product->id,
            name: $product->name,
            slug: $product->slug,
            sku: $product->sku,
            retail_price: number_format((float) $product->retail_price, 2, '.', ','),
            selling_price: number_format((float) $selling_price, 2, '.', ','),
            status: (bool) $product->status,
            is_digital_product: (bool) $product->is_digital_product,
            is_featured: (bool) $product->is_featured,
            is_special_offer: (bool) $product->is_special_offer,
            allow_customer_remarks: (bool) $product->allow_customer_remarks,
            description: $product->description ?? null,
            stock: $product->stock ?? null,
            allow_stocks: $product->allow_stocks ?? null,
            minimum_order_quantity: $product->minimum_order_quantity ?? null,
            allow_guest_purchase: $product->allow_guest_purchase ?? null,
        );
    }
}
