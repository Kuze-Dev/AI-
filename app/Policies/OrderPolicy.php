<?php

declare(strict_types=1);

namespace App\Policies;

use Domain\Customer\Models\Customer;
use Domain\Order\Models\Order;

class OrderPolicy
{
    public function view(Customer $customer, Order $order): bool
    {
        return $order->customer->is($customer);
    }

    public function update(Customer $customer, Order $order): bool
    {
        return $order->customer->is($customer);
    }
}
