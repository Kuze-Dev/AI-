<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Pipes\ServiceOrderMilestoneCreated;

use Domain\ServiceOrder\DataTransferObjects\ServiceBillMilestonePipelineData;
use Domain\ServiceOrder\Jobs\NotifyCustomerLatestServiceBillJob;
use Domain\ServiceOrder\Models\ServiceOrder;

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
