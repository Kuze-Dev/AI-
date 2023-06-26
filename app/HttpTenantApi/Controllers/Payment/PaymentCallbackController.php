<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Payment;

use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Support\Payments\PaymentManager;
use Illuminate\Http\Request;
use PayPal\Api\Payment;
use Spatie\RouteAttributes\Attributes\Get;


class PaymentCallbackController
{
    #[Get('/paymentcallback/{paymentmethod}/{transactionId}/{status}', name: 'payment-callback')]
    public function __invoke(
        string $paymentmethod, 
        string $transactionId, 
        string $status,
        Request $request
        ) 
    {
        
        $PaymentMethod = PaymentMethod::find($paymentmethod);
        
        $paymentManager = PaymentManager::createProvider($PaymentMethod);

        $paymentManager->handleRedirectionCallback($request,$status);
        
        
        
    }
}
