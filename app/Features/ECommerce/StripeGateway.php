<?php

declare(strict_types=1);

namespace App\Features\ECommerce;

use Domain\Tenant\Models\Tenant;

class StripeGateway
{
    public string $name = 'ecommerce.stripe';

    public string $label = 'Stripe';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
