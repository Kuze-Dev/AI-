<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Models\Customer;

class RestoreCustomerAction
{
    public function execute(Customer $customer): ?bool
    {
        return $customer->restore();
    }
}
