<?php

declare(strict_types=1);

namespace App\Features\ECommerce;

use Domain\Tenant\Models\Tenant;

class ECommerceBase
{
    public string $name = 'ecommerce.base';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
