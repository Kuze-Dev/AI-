<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

class CreateCartData
{
    public function __construct(
        public readonly int|string $purchasable_id,
        public readonly string $purchasable_type,
        public readonly int $quantity,
        public readonly ?int $variant_id,
        public readonly ?CartRemarksData $remarks,
    ) {
    }

    public static function fromArray(array $data): self
    {
        $remarksData = isset($data['remarks']) ? CartRemarksData::fromArray($data['remarks']) : null;

        return new self(
            purchasable_id: $data['purchasable_id'],
            purchasable_type: $data['purchasable_type'],
            quantity: (int) $data['quantity'],
            variant_id: $data['variant_id'] ?? null,
            remarks: $remarksData,
        );
    }
}
