<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;

class CreateServiceBillAction
{
    public function __construct(
        private GetServiceBillingAndDueDateAction $getServiceBillingAndDueDateAction
    ) {
    }

    public function execute(
        ServiceOrder|ServiceBill $serviceData,
        ServiceBillData $serviceBillData
    ): ServiceBill {
        $billingDates = $this->getServiceBillingAndDueDateAction->execute($serviceData);

        $serviceBill = ServiceBill::create([
            'service_order_id' => $serviceBillData->service_order_id,
            'bill_date' => $billingDates->bill_date,
            'due_date' => $billingDates->due_date,
            'service_price' => $serviceBillData->service_price,
            'additional_charges' => $serviceBillData->additional_charges,
            'total_amount' => $serviceBillData->total_amount,
            'status' => $serviceBillData->status,
        ]);

        return $serviceBill;
    }
}
