<?php

namespace Domain\ServiceOrder\Jobs;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Actions\CreateServiceBillAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateServiceBillJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private Customer $customer,
        private ServiceBill $serviceBill,
        private CreateServiceBillAction $createServiceBillAction
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $serviceBillData = ServiceBillData::fromArray($this->serviceBill->toArray());

        $serviceBill = $this->createServiceBillAction
            ->execute(
                $this->serviceBill,
                $serviceBillData
            );
    }
}
