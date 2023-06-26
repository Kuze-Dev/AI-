<?php

namespace Domain\Support\Payments\Events;

use Domain\Support\Payments\Models\Payment;
use Illuminate\Queue\SerializesModels;

class PaymentProcessEvent
{
    use SerializesModels;

   
    public $payment;


    public function __construct(Payment $Payment)
    {
        $this->payment = $Payment;
    }
}
