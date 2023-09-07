<?php

declare(strict_types=1);

namespace Domain\Order\Events;

use Domain\Customer\Models\Customer;
use Domain\Order\Models\Order;
use Illuminate\Queue\SerializesModels;

class AdminOrderBankPaymentEvent
{
    use SerializesModels;

    public Customer $customer;
    public Order $order;
    public string $paymentRemarks;

    public function __construct(
        Customer $customer,
        Order $order,
        string $paymentRemarks,
    ) {
        $this->customer = $customer;
        $this->order = $order;
        $this->paymentRemarks = $paymentRemarks;
    }
}
