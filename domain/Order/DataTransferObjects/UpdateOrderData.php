<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class UpdateOrderData
{
    public function __construct(
        public readonly string $type,
        public readonly ?string $status,
        public readonly ?string $notes,
        public readonly ?string $proof_of_payment,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            type: $data['type'],
            status: $data['status'] ?? null,
            notes: $data['notes'] ?? null,
            proof_of_payment: $data['proof_of_payment'] ?? null
        );
    }
}
