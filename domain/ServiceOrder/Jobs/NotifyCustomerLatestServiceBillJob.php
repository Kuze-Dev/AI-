<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Jobs;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Actions\CreateServiceBillAction;
use Domain\ServiceOrder\Actions\SendToCustomerServiceBillEmailAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyCustomerLatestServiceBillJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private Customer $customer,
        private ServiceOrder $serviceOrder
    )
    {
    }

    public function uniqueId(): string
    {
        return $this->customer->getRouteKeyName();
    }

    public function handle(
        SendToCustomerServiceBillEmailAction $sendToCustomerServiceBillEmailAction
    ): void
    {
        $sendToCustomerServiceBillEmailAction->execute(
            $this->customer,
            $this->serviceOrder->latestServiceBill()
        );
    }
}
