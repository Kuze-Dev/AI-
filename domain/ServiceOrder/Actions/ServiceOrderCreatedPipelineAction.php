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
        ServiceOrderCreatedPipelineData $serviceOrderCreatedPipelineData,
        bool $createServiceOrderAddress = true,
    ): void {

        $addressPipe = [];

        if ($createServiceOrderAddress) {
            $addressPipe[] = CreateServiceOrderAddressPipe::class;
        }

        $pipes = [
            ...$addressPipe,
            CreateServiceBillPipe::class,
            NotifyCustomerPipe::class,
        ];

        Pipeline::send($serviceOrderCreatedPipelineData)
            ->through($pipes)
            ->thenReturn();
    }
}
