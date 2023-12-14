<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Events;

use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Queue\SerializesModels;

class AdminServiceBillBankPaymentEvent
{
    use SerializesModels;

    public ServiceBill $serviceBill;

    public string $paymentRemarks;

    public function __construct(
        ServiceBill $serviceBill,
        string $paymentRemarks,
    ) {
        $this->serviceBill = $serviceBill;
        $this->paymentRemarks = $paymentRemarks;
    }
}
