<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Pipes\ServiceOrderMilestoneCreated;

use Domain\ServiceOrder\Actions\CreateServiceBillAction;
use Domain\ServiceOrder\Actions\UpdateServiceBillMilestoneAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillMilestonePipelineData;

class CreateServiceBillMilestonePipe
{
    public function __construct(
        private readonly CreateServiceBillAction $createServiceBillAction,
        private readonly UpdateServiceBillMilestoneAction $updateServiceBillMilestoneAction,
    ) {
    }

    public function handle(
        ServiceBillMilestonePipelineData $serviceBillMilestonePipelineData,
        callable $next
    ): void {
        $updatedValue = $this->updateServiceBillMilestoneAction->execute($serviceBillMilestonePipelineData);
        $this->createServiceBillAction
            ->execute(
                ServiceBillData::paymentMilestone(
                    $serviceBillMilestonePipelineData->service_order,
                    $updatedValue
                )
            );
        $next($serviceBillMilestonePipelineData);

    }
}
