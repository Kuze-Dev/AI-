<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\Taxation\Enums\PriceDisplay;
use Illuminate\Support\Carbon;

class ServiceBillData
{
    public function __construct(
        public readonly int $service_order_id,
        public readonly string $currency,
        public readonly float $service_price,
        public readonly array $additional_charges,
        public readonly float $sub_total,
        public readonly float $tax_percentage,
        public readonly float $tax_total,
        public readonly PriceDisplay|string|null $tax_display,
        public readonly float $total_amount,
        public readonly ServiceBillStatus $status,
        public readonly ?Carbon $bill_date = null,
        public readonly ?Carbon $due_date = null,
    ) {
    }

    public static function paymentMilestone(ServiceOrder $serviceOrder, array $updatedValue): self
    {
        return new self(
            service_order_id: $serviceOrder->id,
            currency: $serviceOrder->currency_code,
            bill_date: null,
            due_date: null,
            service_price: $serviceOrder->service_price,
            additional_charges: $serviceOrder->additional_charges,
            sub_total: $serviceOrder->sub_total,
            tax_display: $serviceOrder->tax_display,
            tax_percentage: $serviceOrder->tax_percentage,
            tax_total: floatval($updatedValue['taxTotal']),
            total_amount: floatval($updatedValue['totalAmount']),
            status: ServiceBillStatus::PENDING,
        );
    }

    public static function initialFromServiceOrder(ServiceOrder $serviceOrder): self
    {
        return new self(
            service_order_id: $serviceOrder->id,
            currency: $serviceOrder->currency_code,
            bill_date: null,
            due_date: null,
            service_price: $serviceOrder->service_price,
            additional_charges: $serviceOrder->additional_charges,
            sub_total: $serviceOrder->sub_total,
            tax_display: $serviceOrder->tax_display,
            tax_percentage: $serviceOrder->tax_percentage,
            tax_total: $serviceOrder->tax_total,
            total_amount: $serviceOrder->total_price,
            status: ServiceBillStatus::PENDING,
        );
    }

    public static function subsequentFromServiceOrderWithAssignedDates(
        ServiceOrder $serviceOrder,
        ServiceOrderBillingAndDueDateData $serviceOrderBillingAndDueDateData
    ): self {

        return new self(
            service_order_id: $serviceOrder->id,
            currency: $serviceOrder->currency_code,
            bill_date: $serviceOrderBillingAndDueDateData->bill_date,
            due_date: $serviceOrderBillingAndDueDateData->due_date,
            service_price: $serviceOrder->service_price,
            additional_charges: $serviceOrder->additional_charges,
            sub_total: $serviceOrder->sub_total,
            tax_display: $serviceOrder->tax_display,
            tax_percentage: $serviceOrder->tax_percentage,
            tax_total: $serviceOrder->tax_total,
            total_amount: $serviceOrder->total_price,
            status: ServiceBillStatus::PENDING,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            service_order_id: $data['service_order_id'],
            currency: $data['currency_code'],
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
