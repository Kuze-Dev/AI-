<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\CreateServiceTransactionData;
use Domain\ServiceOrder\DataTransferObjects\ServiceTransactionData;
use Domain\ServiceOrder\Models\ServiceTransaction;

class CreateServiceTransactionAction
{
    public function execute(CreateServiceTransactionData $createServiceTransactionData): ServiceTransaction
    {
        $serviceTransactionData = $this->prepareTransactionData($createServiceTransactionData);

        $serviceTransaction = ServiceTransaction::create([
            'service_order_id' => $serviceTransactionData->service_order_id,
            'service_bill_id' => $serviceTransactionData->service_bill_id,
            'payment_method_id' => $serviceTransactionData->payment_method_id,
            'total_amount' => $serviceTransactionData->total_amount,
            'currency' => $serviceTransactionData->currency,
            'status' => $serviceTransactionData->status,
        ]);

        return $serviceTransaction;
    }

    private function prepareTransactionData(
        CreateServiceTransactionData $createServiceTransactionData
    ): ServiceTransactionData {

        $serviceBill = $createServiceTransactionData->service_bill;

        /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
        $serviceOrder = $serviceBill->serviceOrder;

        return new ServiceTransactionData(
            service_order_id: $serviceBill->service_order_id,
            service_bill_id: $serviceBill->id,
            payment_method_id: $createServiceTransactionData->payment_method_id,
            total_amount: $serviceBill->total_amount,
            currency: $serviceOrder->currency_code,
            status: $createServiceTransactionData->service_transaction_status,
        );
    }
}
