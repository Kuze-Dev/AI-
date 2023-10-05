<?php

declare(strict_types=1);

namespace App\Features\Customer;

use Domain\Tenant\Models\Tenant;

class AddressBase
{
    public string $name = 'customer.address-base';

    public string $label = 'Address';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
