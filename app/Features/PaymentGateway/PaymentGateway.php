<?php

declare(strict_types=1);

namespace App\Features\PaymentGateway;

use Domain\Tenant\Models\Tenant;

class PaymentGateway
{
    public string $name = 'payment-gateway.base';

    public string $label = 'PaymentGateway';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
