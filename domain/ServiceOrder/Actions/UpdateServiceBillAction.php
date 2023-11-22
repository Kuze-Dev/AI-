<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\UpdateServiceBillData;
use Domain\ServiceOrder\Models\ServiceBill;

class UpdateServiceBillAction
{
    public function execute(ServiceBill $serviceBill, UpdateServiceBillData $updateServiceBillData): ServiceBill
    {
        $serviceBill->update([
            'sub_total' => $updateServiceBillData->sub_total,
            'tax_total' => $updateServiceBillData->tax_total,
            'total_amount' => $updateServiceBillData->total_amount,
            'additional_charges' => $updateServiceBillData->additional_charges,
        ]);

        return $serviceBill;
    }
}
