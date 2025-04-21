<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use Domain\ServiceOrder\Enums\ServiceTransactionStatus;

class ServiceTransactionData
{
    public function __construct(
        public readonly int $service_order_id,
        public readonly ?int $service_bill_id,
        public readonly int $payment_id,
        public readonly int $payment_method_id,
        public readonly string $currency,
        public readonly ?float $total_amount,
        public readonly ServiceTransactionStatus $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            service_order_id: (int) $data['service_order_id'],
            service_bill_id: (int) $data['service_bill_id'],
            payment_id: (int) $data['payment_id'],
            payment_method_id: (int) $data['payment_method_id'],
            currency: $data['currency'],
            total_amount: $data['total_amount'],
            status: $data['status'],
        );
    }
}
