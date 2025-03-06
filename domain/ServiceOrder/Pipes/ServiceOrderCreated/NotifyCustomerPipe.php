<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Pipes\ServiceOrderCreated;

use Domain\ServiceOrder\DataTransferObjects\ServiceOrderCreatedPipelineData;
use Domain\ServiceOrder\Jobs\NotifyCustomerLatestServiceBillJob;

class NotifyCustomerPipe
{
    public function handle(
        ServiceOrderCreatedPipelineData $serviceOrderCreatedPipelineData,
        callable $next
    ): ServiceOrderCreatedPipelineData {

        dispatch(new \Domain\ServiceOrder\Jobs\NotifyCustomerServiceOrderStatusJob($serviceOrderCreatedPipelineData->serviceOrder))
            ->chain([
                new NotifyCustomerLatestServiceBillJob($serviceOrderCreatedPipelineData->serviceOrder),
            ]);

        return $next($serviceOrderCreatedPipelineData);
    }
}
