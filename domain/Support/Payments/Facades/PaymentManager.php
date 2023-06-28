<?php

declare(strict_types=1);

namespace Domain\Support\Payments\Facades;

use Domain\Support\Payments\PaymentManager as PaymentsPaymentManager;
use Illuminate\Support\Facades\Facade;

class PaymentManager extends Facade
{
    public static function getFacadeAccessor()
    {
        return PaymentsPaymentManager::class;
    }
}
