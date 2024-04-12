<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Events;

use Domain\Payments\Models\Payment;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Queue\SerializesModels;

class AdminServiceOrderBankPaymentEvent
{
    use SerializesModels;

    public function __construct(public ServiceOrder $serviceOrder, public string $paymentRemarks, public Payment $payment)
    {
    }
}
