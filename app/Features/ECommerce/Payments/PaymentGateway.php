<?php

declare(strict_types=1);

namespace App\Features\ECommerce\Payments;

use Domain\Tenant\Models\Tenant;

class PaymentGateway
{
    public string $name = 'payment-gateway.base';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
