<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use Carbon\Carbon;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\Taxation\Enums\PriceDisplay;

class ServiceBillData
{
    public function __construct(
        public readonly int $service_order_id,
        public readonly ?string $reference,
        public readonly ?Carbon $bill_date,
        public readonly ?Carbon $due_date,
        public readonly float $service_price,
        public readonly array $additional_charges,
        public readonly float $sub_total,
        public readonly float $tax_percentage,
        public readonly float $tax_total,
        public readonly ?PriceDisplay $tax_display,
        public readonly float $total_amount,
        public readonly ServiceBillStatus $status,
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
            sub_total: $data['sub_total'],
            tax_display: $data['tax_display'],
            tax_percentage: $data['tax_percentage'],
            tax_total: $data['tax_total'],
            total_amount: $data['total_price'],
            status: ServiceBillStatus::PENDING,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            service_order_id: $data['service_order_id'],
            reference: $data['reference'],
            bill_date: $data['bill_date'],
            due_date: $data['due_date'],
            service_price: $data['service_price'],
            additional_charges: $data['additional_charges'],
            sub_total: $data['sub_total'],
            tax_display: $data['tax_display'],
            tax_percentage: $data['tax_percentage'],
            tax_total: $data['tax_total'],
            total_amount: $data['total_price'],
            status: $data['status'],
        );
    }
}
