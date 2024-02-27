<?php

declare(strict_types=1);

namespace App\Features\Shopconfiguration\PaymentGateway;

use App\Features\FeatureContract;
use Domain\Tenant\Models\Tenant;

class BankTransfer implements FeatureContract
{
    public string $name = 'payment-gateway.bank-transfer';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }

    public function getLabel(): string
    {
        return trans('Bank Transfer');
    }
}
