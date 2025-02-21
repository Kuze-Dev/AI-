<?php

declare(strict_types=1);

namespace App\Features\Shopconfiguration\PaymentGateway;

use Domain\Tenant\Models\Tenant;

class VisionpayGateway implements FeatureContract
{
    public string $name = 'payment-gateway.vision-pay';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    #[\Override]
    public function getLabel(): string
    {
        return trans('VisionPay');
    }
}
