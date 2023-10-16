<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Spatie\QueueableAction\QueueableAction;

class SaveServiceBillEmailSentTimestampAction
{
    use QueueableAction;

    public function execute(ServiceBill $serviceBill): void
    {
        if (
            $serviceBill->email_notification_sent_at === null &&
            $serviceBill->status === ServiceBillStatus::FORPAYMENT
        ) {
            $serviceBill->update(['email_notification_sent_at' => now()]);
        }
    }
}
