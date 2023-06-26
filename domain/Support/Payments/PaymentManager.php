<?php

namespace Domain\Support\Payments;

use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Support\Payments\Providers\PaypalProvider;

// use App\Services\Payments\Contracts\PaymentManager as PaymentManagerContract;
// use App\Services\Payments\Providers\Provider;

class PaymentManager 
{
   
    public static function createProvider(PaymentMethod $paymentMethod)
    {
        
        return match ($paymentMethod->gateway) {

            'paypal' => new PaypalProvider($paymentMethod),
        };
    }

 
}
