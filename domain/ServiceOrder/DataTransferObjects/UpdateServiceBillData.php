<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

class UpdateServiceBillData
{
    public function __construct(
        public readonly float $sub_total,
        public readonly float $tax_total,
        public readonly float $total_amount,
        public readonly ?array $additional_charges,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            sub_total: $data['sub_total'],
            tax_total: $data['tax_total'],
            total_amount: $data['total_price'],
            additional_charges: $data['additional_charges'] ?? null,
        );
    }
}
