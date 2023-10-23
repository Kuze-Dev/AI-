<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Jobs;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Actions\CreateServiceBillAction;
use Domain\ServiceOrder\Actions\GetServiceBillingAndDueDateAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateServiceBillJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        /**
         * TODO: to be removed.
         * @phpstan-ignore-next-line
         */
        private Customer $customer,
        private ServiceOrder $serviceOrder,
        private ServiceBill $serviceBill
    ) {
    }

    public function uniqueId(): string
    {
        return $this->serviceOrder->reference;
    }

    public function handle(
        CreateServiceBillAction $createServiceBillAction,
        GetServiceBillingAndDueDateAction $getServiceBillingAndDueDateAction
    ): void
    {
        $serviceBillData = ServiceBillData::fromArray(
            $this->serviceBill->toArray()
        );

        $serviceOrderBillingAndDueDateData = $getServiceBillingAndDueDateAction
            ->execute($this->serviceBill);

        $createServiceBillAction->execute(
            $serviceBillData,
            $serviceOrderBillingAndDueDateData
        );
    }
}
