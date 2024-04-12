<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Pipes\ServiceOrderPaymentUpdated;

use Domain\ServiceOrder\Actions\GenerateServiceTransactionReceiptAction;
use Domain\ServiceOrder\Actions\SendToCustomerServiceTransactionReceiptEmailAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderPaymentUpdatedPipelineData;

class GenerateServiceTransactionReceiptPipe
{
    public function __construct(
        private readonly GenerateServiceTransactionReceiptAction $generateServiceTransactionReceiptAction,
        private readonly SendToCustomerServiceTransactionReceiptEmailAction $sendToCustomerServiceTransactionReceiptEmailAction
    ) {
    }

    public function handle(
        ServiceOrderPaymentUpdatedPipelineData $serviceOrderPaymentUpdatedPipelineData,
        callable $next
    ): ServiceOrderPaymentUpdatedPipelineData {
        if ($serviceOrderPaymentUpdatedPipelineData->is_payment_paid) {
            $pdf = $this->generateServiceTransactionReceiptAction
                ->execute($serviceOrderPaymentUpdatedPipelineData->service_transaction);

            $this->sendToCustomerServiceTransactionReceiptEmailAction
                ->execute(
                    $serviceOrderPaymentUpdatedPipelineData->service_order,
                    $serviceOrderPaymentUpdatedPipelineData->service_bill,
                    $pdf
                );
        }

        return $next($serviceOrderPaymentUpdatedPipelineData);
    }
}
