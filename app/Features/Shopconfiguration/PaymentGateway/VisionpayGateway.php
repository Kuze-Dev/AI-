<?php

declare(strict_types=1);

namespace App\Features\Shopconfiguration\PaymentGateway;

use Domain\Tenant\Models\Tenant;

class VisionpayGateway
{
    public string $name = 'payment-gateway.vision-pay';

    public string $label = 'VisionPay';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
