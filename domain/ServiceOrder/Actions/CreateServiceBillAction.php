<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Support\Str;

class CreateServiceBillAction
{
    public function execute(ServiceBillData $serviceBillData): ServiceBill
    {
        $uniqueReference = null;

        do {
            $referenceNumber = str::upper(str::random(12));

            $existingReference = ServiceBill::where('reference', $referenceNumber)->first();

            if ( ! $existingReference) {
                $uniqueReference = $referenceNumber;

                break;
            }
        } while (true);

        $serviceBill = ServiceBill::create([
            'service_order_id' => $serviceBillData->service_order_id,
            'reference' => $uniqueReference,
            'service_price' => $serviceBillData->service_price,
            'additional_charges' => $serviceBillData->additional_charges,
            'total_amount' => $serviceBillData->total_amount,
            'status' => $serviceBillData->status,
        ]);

        return $serviceBill;
    }
}
