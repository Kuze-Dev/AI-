<?php

declare(strict_types=1);

namespace Domain\Order\Events;

use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\Order\Models\Order;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Queue\SerializesModels;

class OrderPlacedEvent
{
    use SerializesModels;

    public Customer $customer;
    public Order $order;
    public Address $shippingAddress;
    public ShippingMethod $shippingMethod;

    public function __construct(
        Customer $customer,
        Order $order,
        Address $shippingAddress,
        ShippingMethod $shippingMethod
    ) {
        $this->customer = $customer;
        $this->order = $order;
        $this->shippingAddress = $shippingAddress;
        $this->shippingMethod = $shippingMethod;
    }
}
