<?php

declare(strict_types=1);

namespace App\Features\Shopconfiguration\PaymentGateway;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class PaypalGateway implements FeatureContract
{
    public string $name = 'payment-gateway.paypal';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    #[\Override]
    public function getLabel(): string
    {
        return trans('PayPaL');
    }
}
