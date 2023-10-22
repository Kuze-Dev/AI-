<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Models\Customer;
use Domain\Customer\Notifications\RejectedRegistrationNotification;

class SendRejectedEmailAction
{
    public function execute(Customer $customer): bool
    {
        if ($customer->register_status === RegisterStatus::REGISTERED) {
            return false;
        }

        $customer->notify(new RejectedRegistrationNotification());

        return true;
    }
}
