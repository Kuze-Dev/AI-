<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Pipes\ServiceOrderMilestoneCreated;

use Domain\ServiceOrder\DataTransferObjects\ServiceOrderCreatedPipelineData;
use Domain\ServiceOrder\Jobs\NotifyCustomerServiceOrderStatusJob;

class NotifyCustomerServiceOrderPipe
{
    public function handle(
        ServiceOrderCreatedPipelineData $serviceOrderCreatedPipelineData,
        callable $next
    ): void {
        NotifyCustomerServiceOrderStatusJob::dispatch($serviceOrderCreatedPipelineData->serviceOrder);
        $next($serviceOrderCreatedPipelineData);
    }
}
