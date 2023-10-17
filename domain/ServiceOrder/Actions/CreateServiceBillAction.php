<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderBillingAndDueDateData;
use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Support\Str;

class CreateServiceBillAction
{
    public function execute(
        ServiceBillData $serviceBillData,
        ?ServiceOrderBillingAndDueDateData $serviceOrderBillingAndDueDateData = null
    ): ServiceBill {
        $uniqueReference = null;

        do {
            $referenceNumber = str::upper(str::random(12));

            $existingReference = ServiceBill::where('reference', $referenceNumber)->first();

            if ( ! $existingReference) {
                $uniqueReference = $referenceNumber;

                break;
            }
        } while (true);

        $serviceBillData = [
            'service_order_id' => $serviceBillData->service_order_id,
            'reference' => $uniqueReference,
            'service_price' => $serviceBillData->service_price,
            'additional_charges' => $serviceBillData->additional_charges,
            'total_amount' => $serviceBillData->total_amount,
            'status' => $serviceBillData->status,
        ];

        if ($serviceOrderBillingAndDueDateData) {
            $serviceBillData['bill_date'] = $serviceOrderBillingAndDueDateData->bill_date;
            $serviceBillData['due_date'] = $serviceOrderBillingAndDueDateData->due_date;
        }

        $serviceBill = ServiceBill::create($serviceBillData);

        return $serviceBill;
    }
}
