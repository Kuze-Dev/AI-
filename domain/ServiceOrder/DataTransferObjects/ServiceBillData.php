<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use DateTime;

class ServiceBillData
{
    public function __construct(
        public readonly int $service_order_id,
        public readonly int $payment_method_id,
        public readonly DateTime $bill_date,
        public readonly DateTime $due_date,
        public readonly string $service_price,
        public readonly array $additional_charges,
        public readonly float $total_amount,
        public readonly string $status,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            service_order_id: (int) $data['service_order_id'],
            payment_method_id: (int) $data['payment_method_id'],
            bill_date: new DateTime($data['bill_date']),
            due_date: new DateTime($data['due_date']),
            service_price: $data['service_price'],
            additional_charges: $data['additional_charges'],
            total_amount: $data['total_amount'],
            status: $data['status'],
        );
    }
}
