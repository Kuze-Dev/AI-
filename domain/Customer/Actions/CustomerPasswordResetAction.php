<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Models\Customer;

class CustomerPasswordResetAction
{
    public function execute(Customer $customer, string $password): void
    {
        $customer->update([
            'password' => $password,
        ]);
    }
}
