<?php

declare(strict_types=1);

namespace App\Features\PaymentGateway;

use Domain\Tenant\Models\Tenant;

class BankTransfer
{
    public string $name = 'payment-gateway.bank-transfer';

    public string $label = 'Bank Transfer';

    public function resolve(Tenant $scope): mixed
    {
        return false;
    }
}
