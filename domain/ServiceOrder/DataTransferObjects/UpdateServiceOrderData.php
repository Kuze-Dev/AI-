<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

class UpdateServiceOrderData
{
    public function __construct(
        public readonly ?array $additional_charges,
        public readonly ?array $customer_form,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            additional_charges: $data['additional_charges'] ?? null,
            customer_form: $data['customer_form'] ?? null,
        );
    }
}
