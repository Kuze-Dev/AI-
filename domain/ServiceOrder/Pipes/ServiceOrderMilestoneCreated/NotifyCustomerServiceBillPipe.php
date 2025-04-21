<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Pipes\ServiceOrderMilestoneCreated;

use Domain\ServiceOrder\DataTransferObjects\ServiceBillMilestonePipelineData;

class NotifyCustomerServiceBillPipe
{
    public function handle(
        ServiceBillMilestonePipelineData $serviceBillMilestonePipelineData,
        callable $next
    ): void {
        dispatch(new \Domain\ServiceOrder\Jobs\NotifyCustomerLatestServiceBillJob($serviceBillMilestonePipelineData->service_order));
        $next($serviceBillMilestonePipelineData);
    }
}
