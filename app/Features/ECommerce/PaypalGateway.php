<?php

declare(strict_types=1);

namespace App\Features\ECommerce;

use Domain\Tenant\Models\Tenant;

class PaypalGateway
{
    public string $name = 'ecommerce.paypal';

    public string $label = 'PayPaL';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
