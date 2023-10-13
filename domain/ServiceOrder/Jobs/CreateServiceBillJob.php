<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Jobs;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Actions\CreateServiceBillAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateServiceBillJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private Customer $customer,
        private ServiceBill $serviceBill
    ) {
    }

    public function handle(CreateServiceBillAction $createServiceBillAction): void
    {
        $serviceBillData = ServiceBillData::fromArray($this->serviceBill->toArray());

        $serviceBill = $createServiceBillAction
            ->execute(
                $this->serviceBill,
                $serviceBillData
            );
    }
}
