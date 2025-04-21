<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Jobs;

use Domain\ServiceOrder\Actions\SendToCustomerServiceBillDueDateEmailAction;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyCustomerServiceBillDueDateJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private ServiceOrder $serviceOrder,
        private ServiceBill $serviceBill
    ) {}

    public function uniqueId(): string
    {
        return $this->serviceOrder->reference;
    }

    public function handle(
        SendToCustomerServiceBillDueDateEmailAction $sendToCustomerServiceBillDueDateEmailAction
    ): void {
        $sendToCustomerServiceBillDueDateEmailAction->execute(
            $this->serviceOrder,
            $this->serviceBill
        );
    }
}
