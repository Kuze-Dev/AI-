<?php

declare(strict_types=1);

namespace App\Features\Shopconfiguration\PaymentGateway;

use Domain\Tenant\Models\Tenant;

class StripeGateway
{
    public string $name = 'payment-gateway.stripe';

    public string $label = 'Stripe';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
