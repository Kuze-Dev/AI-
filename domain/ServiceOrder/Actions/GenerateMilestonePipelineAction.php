<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceBillMilestonePipelineData;
use Domain\ServiceOrder\Pipes\ServiceOrderMilestoneCreated\CreateServiceBillMilestonePipe;
use Domain\ServiceOrder\Pipes\ServiceOrderMilestoneCreated\NotifyCustomerServiceBillPipe;
use Domain\ServiceOrder\Pipes\ServiceOrderMilestoneCreated\UpdatePaymentPlanPipe;
use Illuminate\Support\Facades\Pipeline;

class GenerateMilestonePipelineAction
{
    public function execute(ServiceBillMilestonePipelineData $serviceBillMilestonePipelineData): void
    {
        Pipeline::send($serviceBillMilestonePipelineData)
            ->through([
                CreateServiceBillMilestonePipe::class,
                UpdatePaymentPlanPipe::class,
                NotifyCustomerServiceBillPipe::class,
            ])
            ->thenReturn();
    }
}
