<?php

declare(strict_types=1);

namespace App\Policies;

use Domain\Cart\Models\Cart;
use Domain\Customer\Models\Customer;

class CartPolicy
{
    public function view(Customer $customer, Cart $cart): bool
    {
        return $cart->customer->is($customer);
    }

    public function create(Customer $customer, Cart $cart): bool
    {
        return $cart->customer->is($customer);
    }

    public function update(Customer $customer, Cart $cart): bool
    {
        return $cart->customer->is($customer);
    }

    public function delete(Customer $customer, Cart $cart): bool
    {
        return $cart->customer->is($customer);
    }
}
