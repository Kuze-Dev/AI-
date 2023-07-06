<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

class CartStoreData
{
    public function __construct(
        public readonly int $customer_id,
        public readonly int $purchasable_id,
        public readonly string $purchasable_type,
        public readonly int $quantity,
        public readonly mixed $variant,
        public readonly ?string $notes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            customer_id: $data['customer_id'],
            purchasable_id: $data['purchasable_id'],
            purchasable_type: $data['purchasable_type'],
            quantity: $data['quantity'],
            variant: $data['variant'] ?? null,
            notes: $data['notes'] ?? null,
        );
    }
}
