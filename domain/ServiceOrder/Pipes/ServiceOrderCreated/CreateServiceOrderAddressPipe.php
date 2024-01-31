<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Pipes\ServiceOrderCreated;

use Domain\ServiceOrder\Actions\CreateServiceOrderAddressAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAddressData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderCreatedPipelineData;

class CreateServiceOrderAddressPipe
{
    public function __construct(
        private CreateServiceOrderAddressAction $createServiceOrderAddressAction
    ) {
    }

    public function handle(
        ServiceOrderCreatedPipelineData $serviceOrderCreatedPipelineData,
        callable $next
    ): void {

        $this->createServiceOrderAddressAction->execute(
            new ServiceOrderAddressData(
                serviceOrder: $serviceOrderCreatedPipelineData->serviceOrder,
                service_address_id: $serviceOrderCreatedPipelineData->service_address_id,
                billing_address_id: $serviceOrderCreatedPipelineData->billing_address_id,
                is_same_as_billing: $serviceOrderCreatedPipelineData->is_same_as_billing,
            )
        );

        $next($serviceOrderCreatedPipelineData);
    }
}
