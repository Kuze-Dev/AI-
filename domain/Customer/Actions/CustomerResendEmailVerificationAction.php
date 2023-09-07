<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Models\Customer;

class CustomerResendEmailVerificationAction
{
    public function execute(Customer $customer): bool
    {
        if ($customer->hasVerifiedEmail()) {
            return false;
        }

        $customer->sendEmailVerificationNotification();

        return true;
    }
}
