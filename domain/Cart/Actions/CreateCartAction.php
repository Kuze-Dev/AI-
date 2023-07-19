<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\Models\Cart;
use Domain\Customer\Models\Customer;

class CreateCartAction
{
    public function execute(Customer $customer): Cart
    {
        $cart = Cart::firstOrCreate([
            'customer_id' => $customer->id,
        ]);

        return $cart;
    }
}
