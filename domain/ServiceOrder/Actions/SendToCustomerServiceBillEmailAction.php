<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Customer\Models\Customer;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Notifications\ServiceBillNotification;

class SendToCustomerServiceBillEmailAction
{
    public function execute(Customer $customer, ServiceBill $serviceBill): void
    {
        $customer->notify(new ServiceBillNotification($serviceBill));
    }
}
