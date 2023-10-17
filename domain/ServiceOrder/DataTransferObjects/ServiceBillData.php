<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use Carbon\Carbon;
use Domain\ServiceOrder\Enums\ServiceBillStatus;

class ServiceBillData
{
    public function __construct(
        public readonly int $service_order_id,
        public readonly ?string $reference,
        public readonly ?Carbon $bill_date,
        public readonly ?Carbon $due_date,
        public readonly float $service_price,
        public readonly array $additional_charges,
        public readonly float $total_amount,
        public readonly ServiceBillStatus $status,
        public readonly ?Carbon $email_notification_sent_at = null
    ) {
    }

    public static function fromCreatedServiceOrder(array $data): self
    {
        return new self(
            service_order_id: isset($data['service_order_id']) ? (int) $data['service_order_id'] : (int) $data['id'],
            reference: null,
            bill_date: null,
            due_date: null,
            service_price: $data['service_price'],
            additional_charges: $data['additional_charges'],
            total_amount: $data['total_price'],
            status: ServiceBillStatus::PENDING,
            email_notification_sent_at: null
        );
    }
}
