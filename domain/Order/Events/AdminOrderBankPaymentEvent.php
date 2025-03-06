<?php

declare(strict_types=1);

namespace Domain\Order\Events;

use Domain\Customer\Models\Customer;
use Domain\Order\Models\Order;
use Illuminate\Queue\SerializesModels;

class AdminOrderBankPaymentEvent
{
    use SerializesModels;

    public function __construct(public Customer $customer, public Order $order, public string $paymentRemarks) {}
}
