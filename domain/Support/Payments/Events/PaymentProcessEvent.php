<?php

declare(strict_types=1);

namespace Domain\Support\Payments\Events;

use Domain\Support\Payments\Models\Payment;
use Illuminate\Queue\SerializesModels;

class PaymentProcessEvent
{
    use SerializesModels;

    public Payment $payment;

    public function __construct(Payment $Payment)
    {
        $this->payment = $Payment;

    }
}
