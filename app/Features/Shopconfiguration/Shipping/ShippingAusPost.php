<?php

declare(strict_types=1);

namespace App\Features\Shopconfiguration\Shipping;

use Domain\Tenant\Models\Tenant;

class ShippingAusPost
{
    public string $name = 'ecommerce.auspost';

    public string $label = 'Shipping - Auspost';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
