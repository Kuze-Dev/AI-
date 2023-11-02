<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceOrderCreatedPipelineData;
use Domain\ServiceOrder\Pipes\ServiceOrderCreated\CreateServiceBillPipe;
use Domain\ServiceOrder\Pipes\ServiceOrderCreated\CreateServiceOrderAddressPipe;
use Domain\ServiceOrder\Pipes\ServiceOrderCreated\NotifyCustomerPipe;
use Illuminate\Support\Facades\Pipeline;

class ServiceOrderCreatedPipelineAction
{
    public function execute(
        ServiceOrderCreatedPipelineData $serviceOrderCreatedPipelineData
    ): void {

        Pipeline::send($serviceOrderCreatedPipelineData)
            ->through([
                CreateServiceOrderAddressPipe::class,
                CreateServiceBillPipe::class,
                NotifyCustomerPipe::class,
            ])
            ->thenReturn();
    }
}
