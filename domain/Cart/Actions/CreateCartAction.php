<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\Models\Cart;
use Domain\Customer\Models\Customer;
use Illuminate\Support\Str;

class CreateCartAction
{
    public function execute(Customer $customer): Cart
    {
        $cart = Cart::where([
            'customer_id' => $customer->id,
        ])->first();

        if ($cart) {
            return $cart;
        }

        $newCart = Cart::firstOrCreate([
            'uuid' => (string) Str::uuid(),
            'customer_id' => $customer->id,
        ]);

        return $newCart;
    }
}
