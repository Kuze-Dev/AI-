<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceOrderCreatedPipelineData;
use Domain\ServiceOrder\Pipes\ServiceOrderCreated\CreateServiceOrderAddressPipe;
use Domain\ServiceOrder\Pipes\ServiceOrderMilestoneCreated\NotifyCustomerServiceOrderPipe;
use Illuminate\Support\Facades\Pipeline;

class ServiceOrderMilestoneCreatedPipelineAction
{
    public function execute(
        ServiceOrderCreatedPipelineData $serviceOrderCreatedPipelineData
    ): void {

        Pipeline::send($serviceOrderCreatedPipelineData)
            ->through([
                CreateServiceOrderAddressPipe::class,
                NotifyCustomerServiceOrderPipe::class,
            ])
            ->thenReturn();
    }
}
