<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

class UpdateServiceOrderData
{
    public function __construct(
        public readonly float $sub_total,
        public readonly float $tax_total,
        public readonly float $total_price,
        public readonly ?array $additional_charges,
        public readonly ?array $customer_form,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sub_total: $data['sub_total'],
            tax_total: $data['tax_total'],
            total_price: $data['total_price'],
            additional_charges: $data['additional_charges'] ?? null,
            customer_form: $data['customer_form'] ?? null,
        );
    }
}
