<?php

declare(strict_types=1);


namespace Domain\Support\Payments\Actions;

use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Support\Payments\DataTransferObjects\PaypalAmountData;
use Domain\Support\Payments\DataTransferObjects\PaypalDetailsData;
use Domain\Support\Payments\DataTransferObjects\PayPalProviderData;
use Domain\Support\Payments\DataTransferObjects\TransactionData;
use Domain\Support\Payments\Interfaces\HandlesManual;
use Domain\Support\Payments\Interfaces\HandlesRedirection;
use Domain\Support\Payments\PaymentManager;
use Illuminate\Database\Eloquent\Model;

class CreatePaymentAction
{
    /** Execute create collection query. */
    public function execute(PaymentMethod $paymentMethod, PayPalProviderData $paypalProviderData)
    {
        
        $paymentManager = PaymentManager::createProvider($paymentMethod);

        if ($paymentManager instanceof HandlesRedirection) {
            
            $paymentManager->initRedirection($paypalProviderData);
        }
        elseif ($paymentManager instanceof HandlesManual) {

            $paymentManager->handleManually($paypalProviderData);
        }


    }
}
