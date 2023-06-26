<?php

namespace Domain\Support\Payments\DataTransferObjects;

use Illuminate\Database\Eloquent\Model;

class ProcessPaymentData
{
    public function __construct(
        public readonly Model $model,
        public readonly PayPalProviderData $paymentData,
         
    ) {
    }

  
}
