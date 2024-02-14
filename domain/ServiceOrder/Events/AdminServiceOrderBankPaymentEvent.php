<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Events;

use Domain\Payments\Models\Payment;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Queue\SerializesModels;

class AdminServiceOrderBankPaymentEvent
{
    use SerializesModels;

    public ServiceOrder $serviceOrder;

    public string $paymentRemarks;

    public Payment $payment;

    public function __construct(
        ServiceOrder $serviceOrder,
        string $paymentRemarks,
        Payment $payment
    ) {
        $this->serviceOrder = $serviceOrder;
        $this->paymentRemarks = $paymentRemarks;
        $this->payment = $payment;
    }
}
