<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;

class CreateServiceBillAction
{
    public function __construct(
    ) {
    }

    public function execute(ServiceBillData $serviceBillData): ServiceOrder
    {
        $serviceOrder = ServiceBill::create([
            'service_id' => $serviceBillData->service_order_id,
            'payment_method_id' => $serviceBillData->payment_method_id,
            'bill_date' => $serviceBillData->bill_date,
            'due_date' => $serviceBillData->due_date,
            'service_price' => $serviceBillData->service_price,
            'additional_charges' => $serviceBillData->additional_charges,
            'total_amount' => $serviceBillData->total_amount,
            'status' => $serviceBillData->status,
        ]);
        return $serviceOrder;
    }
}
