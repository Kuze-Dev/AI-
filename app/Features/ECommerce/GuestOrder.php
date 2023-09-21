<?php

declare(strict_types=1);

namespace App\Features\ECommerce;

use Domain\Tenant\Models\Tenant;

class GuestOrder
{
    public string $name = 'ecommerce.guest-order';

    public string $label = 'Guest Order';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
