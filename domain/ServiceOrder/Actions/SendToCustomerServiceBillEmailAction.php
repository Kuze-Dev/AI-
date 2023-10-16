<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Notifications\ServiceBillNotification;
use Spatie\QueueableAction\QueueableAction;

class SendToCustomerServiceBillEmailAction
{
    use QueueableAction;

    public function execute(Customer $customer, ServiceBill $serviceBill): bool
    {
        $customer->notify(new ServiceBillNotification($serviceBill));

        return true;
    }
}
