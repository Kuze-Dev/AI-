<?php

namespace App\Policies;

use Domain\Cart\Models\CartLine;
use Domain\Customer\Models\Customer;

class CartLinePolicy
{
    public function view(Customer $customer, CartLine $cartLine): bool
    {
        return $cartLine->cart->customer->is($customer);
    }

    public function create(Customer $customer, CartLine $cartLine): bool
    {
        return $cartLine->cart->customer->is($customer);
    }

    public function update(Customer $customer, CartLine $cartLine): bool
    {
        return $cartLine->cart->customer->is($customer);
    }

    public function delete(Customer $customer, CartLine $cartLine): bool
    {
        return $cartLine->cart->customer->is($customer);
    }
}
