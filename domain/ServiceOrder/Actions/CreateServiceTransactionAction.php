<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceTransactionData;
use Domain\ServiceOrder\Models\serviceTransaction;

class CreateServiceTransactionAction
{
    public function __construct(
    ) {
    }

    public function execute(
        ServiceTransactionData $serviceTransactionData
    ): serviceTransaction {

        $serviceTransaction = serviceTransaction::create([
            // 'service_order_id' => $serviceTransactionData->service_order_id,
            // 'bill_date' => $billingDates->bill_date,
            // 'due_date' => $billingDates->due_date,
            // 'service_price' => $serviceTransactionData->service_price,
            // 'additional_charges' => $serviceTransactionData->additional_charges,
            // 'total_amount' => $serviceTransactionData->total_amount,
            // 'status' => $serviceTransactionData->status,
        ]);

        
        return $serviceTransaction;
    }
}
