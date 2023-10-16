<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Listeners;

use Domain\ServiceOrder\Actions\SaveServiceBillEmailSentTimestampAction;
use Domain\ServiceOrder\Events\ServiceBillDueDateNotificationSentEvent;

class ServiceBillDueDateNotificationSentListener
{
    public function __construct(
        private SaveServiceBillEmailSentTimestampAction $saveServiceBillEmailSentTimestampAction
    )
    {
    }

    public function handle(ServiceBillDueDateNotificationSentEvent $event): void
    {
        $this->saveServiceBillEmailSentTimestampAction->execute($event->serviceBill);
    }
}
