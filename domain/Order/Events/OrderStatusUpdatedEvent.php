<?php

declare(strict_types=1);

namespace Domain\Order\Events;

use Domain\Customer\Models\Customer;
use Domain\Order\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedEvent
{
    use SerializesModels;

    public Customer $customer;
    public Order $order;
    public string $status;

    public function __construct(
        Customer $customer,
        Order $order,
        string $status
    ) {
        $this->customer = $customer;
        $this->order = $order;
        $this->status = $status;
    }
}
