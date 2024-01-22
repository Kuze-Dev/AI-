<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Pipes\ServiceOrderMilestoneCreated;

use Domain\ServiceOrder\DataTransferObjects\ServiceBillMilestonePipelineData;
use Domain\ServiceOrder\Jobs\NotifyCustomerLatestServiceBillJob;

class NotifyCustomerServiceBillPipe
{
    public function handle(
        ServiceBillMilestonePipelineData $serviceBillMilestonePipelineData,
        callable $next
    ): void {
        NotifyCustomerLatestServiceBillJob::dispatch($serviceBillMilestonePipelineData->service_order);
        $next($serviceBillMilestonePipelineData);
    }
}
