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
        return ServiceTransaction::create(
            (array) new ServiceTransactionData(
                service_order_id: $createServiceTransactionData->service_order->id,
                service_bill_id: $createServiceTransactionData->service_bill?->id ?? null,
                payment_id: $createServiceTransactionData->payment->id,
                payment_method_id: $createServiceTransactionData->payment->payment_method_id,
                total_amount: $createServiceTransactionData->service_bill?->total_amount ?? $createServiceTransactionData->total_amount,
                currency: $createServiceTransactionData->service_order->currency_code,
                status: $createServiceTransactionData->service_transaction_status,
            )
        );
    }
}
