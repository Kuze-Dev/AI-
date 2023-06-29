<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Models\Customer;
use Domain\Customer\Notifications\ResetPassword;

class CustomerSendPasswordResetAction
{
    public function execute(Customer $customer, string $token): void
    {
        $customer->notify(new ResetPassword($token));
    }
}
