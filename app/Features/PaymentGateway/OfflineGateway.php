<?php

declare(strict_types=1);

namespace App\Features\PaymentGateway;

use Domain\Tenant\Models\Tenant;

class OfflineGateway
{
    public string $name = 'payment-gateway.offline';

    public string $label = 'Offline Payment';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
