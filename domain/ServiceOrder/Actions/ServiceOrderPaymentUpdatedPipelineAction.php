<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceOrderPaymentUpdatedPipelineData;
use Domain\ServiceOrder\Pipes\ServiceOrderPaymentUpdated\CreateServiceBillPipe;
use Domain\ServiceOrder\Pipes\ServiceOrderPaymentUpdated\GenerateServiceTransactionReceiptPipe;
use Domain\ServiceOrder\Pipes\ServiceOrderPaymentUpdated\UpdateServiceOrderStatusPipe;
use Illuminate\Support\Facades\Pipeline;

class ServiceOrderPaymentUpdatedPipelineAction
{
    public function execute(
        ServiceOrderPaymentUpdatedPipelineData $serviceOrderPaymentUpdatedPipelineData
    ): void {
        Pipeline::send($serviceOrderPaymentUpdatedPipelineData)
            ->through([
                GenerateServiceTransactionReceiptPipe::class,
                CreateServiceBillPipe::class,
                UpdateServiceOrderStatusPipe::class,
            ])
            ->thenReturn();
    }
}
