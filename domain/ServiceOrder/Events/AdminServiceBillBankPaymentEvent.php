<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Events;

use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Queue\SerializesModels;

class AdminServiceBillBankPaymentEvent
{
    use SerializesModels;

    public function __construct(public ServiceBill $serviceBill, public string $paymentRemarks)
    {
    }
}
