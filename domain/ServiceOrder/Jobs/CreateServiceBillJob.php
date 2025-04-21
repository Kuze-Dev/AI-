<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Jobs;

use Domain\ServiceOrder\Actions\CreateServiceBillAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderBillingAndDueDateData;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateServiceBillJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private ServiceOrder $serviceOrder,
        private ServiceOrderBillingAndDueDateData $serviceOrderBillingAndDueDateData
    ) {}

    public function uniqueId(): string
    {
        return $this->serviceOrder->reference;
    }

    public function handle(CreateServiceBillAction $createServiceBillAction): void
    {
        $createServiceBillAction->execute(
            ServiceBillData::subsequentFromServiceOrderWithAssignedDates(
                serviceOrder: $this->serviceOrder,
                serviceOrderBillingAndDueDateData: $this->serviceOrderBillingAndDueDateData
            )
        );
    }
}
