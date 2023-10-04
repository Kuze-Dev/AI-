<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Models\ServiceBill;

class CreateServiceBillAction
{
    public function execute(ServiceBillData $serviceBillData): ServiceBill
    {
        return ServiceBill::create([
            'service_order_id' => $serviceBillData->service_order_id,
            'payment_method_id' => $serviceBillData->payment_method_id,
            'bill_date' => $serviceBillData->bill_date,
            'due_date' => $serviceBillData->due_date,
            'service_price' => $serviceBillData->service_price,
            'additional_charges' => $serviceBillData->additional_charges,
            'total_amount' => $serviceBillData->total_amount,
            'status' => $serviceBillData->status,
        ]);
    }
}
