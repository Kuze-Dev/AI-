<?php

declare(strict_types=1);

namespace App\Features\Shopconfiguration\PaymentGateway;

use Domain\Tenant\Models\Tenant;

class PaypalGateway
{
    public string $name = 'payment-gateway.paypal';

    public string $label = 'PayPaL';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
