<?php

declare(strict_types=1);

namespace Domain\Order\Events;

use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\Order\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderPlacedEvent
{
    use SerializesModels;

    public Customer $customer;
    public Order $order;
    public Address $shippingAddress;

    public function __construct(Customer $customer, Order $order, Address $shippingAddress)
    {
        $this->customer = $customer;
        $this->order = $order;
        $this->shippingAddress = $shippingAddress;
    }
}
