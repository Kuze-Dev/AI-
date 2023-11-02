<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\ServiceOrder\DataTransferObjects\ServiceTransactionData;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceTransaction;

class CreateServiceTransactionAction
{
    public function execute(array $data, PaymentMethod $paymentMethod): ServiceTransaction
    {
        $serviceTransactionData = $this->prepareTransactionData($data, $paymentMethod);

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
        array $data,
        PaymentMethod $paymentMethod
    ): ServiceTransactionData {

        $serviceBill = ServiceBill::whereReference($data['reference_id'])->firstOrFail();

        /** @var \Domain\ServiceOrder\Models\ServiceOrder $serviceOrder */
        $serviceOrder = $serviceBill->serviceOrder;

        $newData = [
            'service_order_id' => $serviceBill->service_order_id,
            'service_bill_id' => $serviceBill->id,
            'payment_method_id' => $paymentMethod->id,
            'total_amount' => $serviceBill->total_amount,
            'currency' => $serviceOrder->currency_code,
            'status' => ServiceTransactionStatus::PENDING,
        ];

        $serviceTransactionData = ServiceTransactionData::fromArray($newData);

        return $serviceTransactionData;
    }
}
