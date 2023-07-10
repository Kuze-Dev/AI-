<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Models\Customer;

class EditCustomerPasswordAction
{
    public function execute(Customer $customer, string $password): Customer
    {
        $customer->update([
            'password' => $password,
        ]);

        return $customer;
    }
}
