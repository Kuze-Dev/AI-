<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Pipes\ServiceOrderPaymentUpdated;

use Domain\ServiceOrder\Actions\GenerateServiceTransactionReceiptAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderPaymentUpdatedPipelineData;

class GenerateServiceTransactionReceiptPipe
{
    public function __construct(
        private GenerateServiceTransactionReceiptAction $generateServiceTransactionReceiptAction
    ) {
    }

    public function handle(
        ServiceOrderPaymentUpdatedPipelineData $serviceOrderPaymentUpdatedPipelineData,
        callable $next
    ): ServiceOrderPaymentUpdatedPipelineData {
        $serviceTransaction = $serviceOrderPaymentUpdatedPipelineData->service_transaction;

        if ($serviceOrderPaymentUpdatedPipelineData->is_payment_paid) {
            $pdf = $this->generateServiceTransactionReceiptAction
                ->execute($serviceTransaction);
        }

        return $next($serviceOrderPaymentUpdatedPipelineData);
    }
}
