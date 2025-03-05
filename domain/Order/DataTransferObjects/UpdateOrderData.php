<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

readonly class UpdateOrderData
{
    public function __construct(
        public string $type,
        public ?string $status,
        public ?string $notes,
        public ?string $proof_of_payment,
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
