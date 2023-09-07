<?php

declare(strict_types=1);

namespace App\Policies;

use Domain\Cart\Models\Cart;
use Domain\Customer\Models\Customer;

class CartPolicy
{
    public function view(Customer $customer, Cart $cart): bool
    {
        if ($cart->customer) {
            return $cart->customer->is($customer);
        }

        return false;
    }

    public function create(Customer $customer, Cart $cart): bool
    {
        if ($cart->customer) {
            return $cart->customer->is($customer);
        }

        return false;
    }

    public function update(Customer $customer, Cart $cart): bool
    {
        if ($cart->customer) {
            return $cart->customer->is($customer);
        }

        return false;
    }

    public function delete(Customer $customer, Cart $cart): bool
    {
        if ($cart->customer) {
            return $cart->customer->is($customer);
        }

        return false;
    }
}
