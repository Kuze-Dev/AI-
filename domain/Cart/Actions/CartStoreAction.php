<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CartStoreData;
use Domain\Cart\Enums\CartActionResult;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Exception;

class CartStoreAction
{
    public function execute(CartStoreData $cartData): CartActionResult|Exception
    {
        $customerCart = Cart::where('customer_id', $cartData->customer_id)->first();

        if ($customerCart) {
            return $this->createCartLine($customerCart, $cartData);
        }

        $cart = Cart::create([
            'customer_id' => $cartData->customer_id,
        ]);

        return $this->createCartLine($cart, $cartData);
    }

    private function createCartLine(Cart $cart, CartStoreData $cartData): CartActionResult|Exception
    {
        $result = app(CreateCartLineAction::class)->execute($cart, $cartData);

        if ($result instanceof CartLine) {
            return CartActionResult::SUCCESS;
        }

        return $result;
    }
}
