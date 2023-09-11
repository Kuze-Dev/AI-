<?php

declare(strict_types=1);

namespace App\Features\ECommerce;

use Domain\Tenant\Models\Tenant;

class ShippingUsps
{
    public string $name = 'ecommerce.usps';

    public string $label = 'Shipping - USPS';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
