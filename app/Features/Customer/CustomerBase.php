<?php

declare(strict_types=1);

namespace App\Features\Customer;

use Domain\Tenant\Models\Tenant;

class CustomerBase
{
    public string $name = 'customer.base';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
