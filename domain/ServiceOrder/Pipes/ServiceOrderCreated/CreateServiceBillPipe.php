<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Pipes\ServiceOrderCreated;

use Domain\ServiceOrder\Actions\CreateServiceBillAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderCreatedPipelineData;

class CreateServiceBillPipe
{
    public function __construct(
        private readonly CreateServiceBillAction $createServiceBillAction
    ) {}

    public function handle(
        ServiceOrderCreatedPipelineData $serviceOrderCreatedPipelineData,
        callable $next
    ): ServiceOrderCreatedPipelineData {

        if (! $serviceOrderCreatedPipelineData->serviceOrder->needs_approval) {
            $this->createServiceBillAction
                ->execute(
                    ServiceBillData::initialFromServiceOrder(
                        $serviceOrderCreatedPipelineData->serviceOrder
                    )
                );
        }

        return $next($serviceOrderCreatedPipelineData);
    }
}
