<?php

declare(strict_types=1);

namespace Domain\Payments\Facades;

use Domain\Payments\PaymentManager as PaymentsPaymentManager;
use Illuminate\Support\Facades\Facade;

class PaymentManager extends Facade
{
    #[\Override]
    public static function getFacadeAccessor()
    {
        return PaymentsPaymentManager::class;
    }
}
