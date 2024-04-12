<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Pipes\ServiceOrderMilestoneCreated;

use Domain\ServiceOrder\Actions\UpdatePaymentPlanAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillMilestonePipelineData;

class UpdatePaymentPlanPipe
{
    public function __construct(
        private readonly UpdatePaymentPlanAction $updatePaymentPlanAction,
    ) {
    }

    public function handle(
        ServiceBillMilestonePipelineData $serviceBillMilestonePipelineData,
        callable $next
    ): void {
        $this->updatePaymentPlanAction->execute($serviceBillMilestonePipelineData);
        $next($serviceBillMilestonePipelineData);
    }
}
