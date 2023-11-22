<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Notifications\ServiceBillNotification;
use Illuminate\Support\Facades\Notification;

class SendToCustomerServiceBillEmailAction
{
    public function execute(ServiceOrder $serviceOrder, ServiceBill $serviceBill): void
    {
        Notification::route('mail', [
            $serviceOrder->customer_email => $serviceOrder->customer_full_name,
        ])->notify(new ServiceBillNotification($serviceBill));
    }
}
