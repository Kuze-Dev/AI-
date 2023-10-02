<?php

declare(strict_types=1);

namespace App\Features\ECommerce;

use Domain\Tenant\Models\Tenant;

class GuestPurchase
{
    public string $name = 'ecommerce.guest-purchase';

    public string $label = 'Guest Purchase';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
