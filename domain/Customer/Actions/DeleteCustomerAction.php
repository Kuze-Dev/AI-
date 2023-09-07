<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Models\Customer;

class DeleteCustomerAction
{
    public function execute(Customer $customer): ?bool
    {
        return $customer->delete();
    }
}
