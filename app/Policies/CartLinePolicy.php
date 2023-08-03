<?php

declare(strict_types=1);

namespace App\Policies;

use Domain\Cart\Models\CartLine;
use Domain\Customer\Models\Customer;

class CartLinePolicy
{
    public function view(Customer $customer, CartLine $cartLine): bool
    {
        if ($cartLine->cart && $cartLine->cart->customer) {
            return $cartLine->cart->customer->is($customer);
        }

        return false;
    }

    public function create(Customer $customer, CartLine $cartLine): bool
    {
        if ($cartLine->cart && $cartLine->cart->customer) {
            return $cartLine->cart->customer->is($customer);
        }

        return false;
    }

    public function update(Customer $customer, CartLine $cartLine): bool
    {
        if ($cartLine->cart && $cartLine->cart->customer) {
            return $cartLine->cart->customer->is($customer);
        }

        return false;
    }

    public function delete(Customer $customer, CartLine $cartLine): bool
    {
        if ($cartLine->cart && $cartLine->cart->customer) {
            return $cartLine->cart->customer->is($customer);
        }

        return false;
    }
}
