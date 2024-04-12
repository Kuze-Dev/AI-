<?php

declare(strict_types=1);

namespace Domain\Payments\Events;

use Domain\Payments\Models\Payment;
use Illuminate\Queue\SerializesModels;

class PaymentProcessEvent
{
    use SerializesModels;

    public function __construct(public Payment $payment)
    {
    }
}
