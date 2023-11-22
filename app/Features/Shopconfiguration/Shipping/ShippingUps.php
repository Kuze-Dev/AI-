<?php

declare(strict_types=1);

namespace App\Features\Shopconfiguration\Shipping;

use Domain\Tenant\Models\Tenant;

class ShippingUps
{
    public string $name = 'ecommerce.ups';

    public string $label = 'Shipping - UPS';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
