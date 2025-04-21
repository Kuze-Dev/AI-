<?php

declare(strict_types=1);

namespace Domain\Payments\DataTransferObjects;

class ItemsData
{
    public function __construct(
        public readonly ?string $sku = null,
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?string $quantity = null,
        public readonly ?string $price = null,
        public readonly ?string $currency = null,
        public readonly ?string $tax = null,
        public readonly ?string $url = null,
        public readonly ?string $category = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sku: $data['sku'] ?? null,
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            quantity: $data['quantity'] ?? null,
            price: $data['price'] ?? null,
            currency: $data['currency'] ?? null,
            tax: $data['tax'] ?? null,
            url: $data['url'] ?? null,
            category: $data['category'] ?? null,
        );
    }
}
