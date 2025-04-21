<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Models\Customer;
use Domain\Customer\Notifications\ApprovedRegistrationNotification;

class SendApprovedEmailAction
{
    public function execute(Customer $customer): bool
    {
        $customer->notify(new ApprovedRegistrationNotification);

        return true;
    }
}
