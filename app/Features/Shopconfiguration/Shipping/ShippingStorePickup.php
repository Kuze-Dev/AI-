<?php

declare(strict_types=1);

namespace App\Features\Shopconfiguration\Shipping;

use Domain\Tenant\Models\Tenant;

class ShippingStorePickup
{
    public string $name = 'ecommerce.store-pickup';

    public string $label = 'Shipping - Store Pickup';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
