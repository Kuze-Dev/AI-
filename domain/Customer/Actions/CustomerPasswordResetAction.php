<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Models\Customer;
use Domain\Customer\Notifications\PasswordHasBeenReset;

class CustomerPasswordResetAction
{
    public function execute(Customer $customer, string $password): void
    {
        $customer->update([
            'password' => $password,
        ]);

        $customer->notify(new PasswordHasBeenReset());
    }
}
