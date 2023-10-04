<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

class ServiceBillData
{
    public function __construct(
        public readonly int $service_order_id,
        public readonly float $service_price,
        public readonly array $additional_charges,
        public readonly float $total_amount,
        public readonly string $status,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            service_order_id: isset($data['service_order_id']) ? (int) $data['service_order_id'] : (int) $data['id'],
            service_price: $data['service_price'],
            additional_charges: $data['additional_charges'],
            total_amount: $data['total_price'],
            status: $data['status'],
        );
    }
}
