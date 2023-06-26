<?php

namespace Domain\Support\Payments\DataTransferObjects;

use Domain\Support\Payments\Interfaces\PayableInterface;
use Illuminate\Database\Eloquent\Model;

class PayPalProviderData
{
    public function __construct(
        public readonly TransactionData $transactionData,
        // public readonly PaypalAmountData $paymentData,
        public readonly PayableInterface $model,
         
    ) {
    }

  
}
