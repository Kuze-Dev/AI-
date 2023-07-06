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
    public function execute(CartStoreData $cartStoreData): CartActionResult|Exception
    {
        $customerCart = Cart::where('customer_id', $cartStoreData->customer_id)->first();

        if ($customerCart) {
            return $this->createCartLine($customerCart, $cartStoreData);
        }

        $cart = Cart::create([
            'customer_id' => $cartStoreData->customer_id,
        ]);

        return $this->createCartLine($cart, $cartStoreData);
    }

    private function createCartLine(Cart $cart, CartStoreData $cartStoreData): CartActionResult|Exception
    {
        $result = app(CreateCartLineAction::class)->execute($cart, $cartStoreData);

        if ($result instanceof CartLine) {
            return CartActionResult::SUCCESS;
        }

        return $result;
    }
}
