<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\Models\ServiceBill;
use Spatie\QueueableAction\QueueableAction;

class SaveServiceBillEmailSentTimestampAction
{
    use QueueableAction;

    public function execute(ServiceBill $serviceBill): void
    {
        $serviceBill->update(['email_notification_sent_at' => now()]);
    }
}
