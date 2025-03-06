<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

readonly class CreateCartData
{
    public function __construct(
        public int|string $purchasable_id,
        public string $purchasable_type,
        public int $quantity,
        public ?int $variant_id,
        public ?CartRemarksData $remarks,
    ) {}

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
