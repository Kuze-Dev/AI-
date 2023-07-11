<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CreateCartData;
use Domain\Cart\Enums\CartActionResult;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Models\Customer;
use Exception;

class CreateCartAction
{
    public function execute(Customer $customer, CreateCartData $cartData): CartActionResult|Exception
    {
        $cart = Cart::whereCustomerId($customer->id)
            ->whereHas('cart', function ($query) {
                $query->where('user_id', auth()->user()->id);
            })
            ->firstOrCreate([
                'customer_id' => $customer->id,
            ]);

        $result = app(CreateCartLineAction::class)->execute($cart, $cartData);

        if ($result instanceof CartLine) {
            return CartActionResult::SUCCESS;
        }

        return CartActionResult::FAILED;
    }
}
