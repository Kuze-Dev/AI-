<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Models\ServiceBill;

class CreateServiceBillAction
{
    public function __construct(
        private GenerateReferenceNumberAction $generateReferenceNumberAction,
    ) {
    }

    public function execute(ServiceBillData $serviceBillData): ServiceBill
    {
        return ServiceBill::create([
            'service_order_id' => $serviceBillData->service_order_id,
            'reference' => $this->generateReferenceNumberAction
                ->execute(ServiceBill::class),
            'bill_date' => $serviceBillData->bill_date,
            'due_date' => $serviceBillData->due_date,
            'currency' => $serviceBillData->currency,
            'service_price' => $serviceBillData->service_price,
            'additional_charges' => $serviceBillData->additional_charges,
            'sub_total' => $serviceBillData->sub_total,
            'tax_percentage' => $serviceBillData->tax_percentage,
            'tax_display' => $serviceBillData->tax_display,
            'tax_total' => $serviceBillData->tax_total,
            'total_amount' => $serviceBillData->total_amount,
            'total_balance' => $serviceBillData->total_amount,
            'status' => $serviceBillData->status,
        ]);
    }
}
