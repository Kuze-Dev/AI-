<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Domain\Customer\Models\Customer;

class ForceDeleteCustomerAction
{
    public function execute(Customer $customer): ?bool
    {
        return $customer->forceDelete();
    }
}
