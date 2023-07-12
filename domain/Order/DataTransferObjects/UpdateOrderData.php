<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class UpdateOrderData
{
    public function __construct(
        public readonly string $status,
        public readonly ?string $notes,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            status: $data['status'],
            notes: $data['notes'] ?? null
        );
    }
}
