<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Events\ServiceBillDueDateNotificationSentEvent;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Notifications\ServiceBillDueDateNotification;
use Spatie\QueueableAction\QueueableAction;

class SendToCustomerServiceBillDueDateEmailAction
{
    use QueueableAction;

    public function __construct(
        private ServiceBillDueDateNotificationSentEvent $serviceBillDueDateNotificationSentEvent
    ) {
    }

    public function execute(Customer $customer, ServiceBill $serviceBill): void
    {
        $customer->notify(new ServiceBillDueDateNotification($serviceBill));

        $this->serviceBillDueDateNotificationSentEvent->dispatch($serviceBill);
    }
}
