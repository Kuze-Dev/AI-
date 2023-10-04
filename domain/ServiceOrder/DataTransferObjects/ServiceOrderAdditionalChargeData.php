<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

class ServiceOrderAdditionalChargeData
{
    public function __construct(
        public float $selling_price,
        public int $quantity,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            selling_price: (float) $data['selling_price'] ?? null,
            quantity: (int) $data['quantity'],
        );
    }
}
