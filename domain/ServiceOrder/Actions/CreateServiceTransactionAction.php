<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceTransactionData;
use Domain\ServiceOrder\Models\serviceTransaction;

class CreateServiceTransactionAction
{
    public function __construct()
    {
    }

    public function execute(
        ServiceTransactionData $serviceTransactionData
    ): serviceTransaction {

        $serviceTransaction = serviceTransaction::create([
            // 'service_order_id' => $serviceTransactionData->service_order_id,
            // 'service_bill_id' => $ServiceTransactionData->service_bill_id,
            // 'payment_id' => $ServiceTransactionData->payment_id,
            // 'payment_method_id' => $ServiceTransactionData->payment_method_id,
            // 'total_amount' => $ServiceTransactionData->total_amount,
            // 'status' => $ServiceTransactionData->status,
        ]);


        return $serviceTransaction;
    }
}
