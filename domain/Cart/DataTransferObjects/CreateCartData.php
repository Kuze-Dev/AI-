<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

class CreateCartData
{
    public function __construct(
        public readonly int $purchasable_id,
        public readonly string $purchasable_type,
        public readonly int $quantity,
        public readonly ?int $variant_id,
        public readonly ?array $medias,
        public readonly mixed $remarks,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            purchasable_id: (int) $data['purchasable_id'],
            purchasable_type: $data['purchasable_type'],
            quantity: (int) $data['quantity'],
            variant_id: (int) $data['variant_id'] ?? null,
            medias: $data['media'] ?? null,
            remarks: $data['remarks'] ?? null,
        );
    }
}
