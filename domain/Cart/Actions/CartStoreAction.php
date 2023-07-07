<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\CartStoreData;
use Domain\Cart\Enums\CartActionResult;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Models\Customer;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Log;

class CartStoreAction
{
    public function execute(CartStoreData $cartData): CartActionResult|Exception
    {
        $customer = Customer::where("id", $cartData->customer_id)->whereStatus('active')->first();

        if (!$customer) {
            return CartActionResult::FAILED;
        }

        $customerCart = Cart::whereCustomerId($cartData->customer_id)->first();

        if ($customerCart) {
            return $this->createCartLine($customerCart, $cartData);
        }

        $cart = Cart::create([
            'customer_id' => $cartData->customer_id,
        ]);

        return $this->createCartLine($cart, $cartData);
    }

    private function createCartLine(Cart $cart, CartStoreData $cartData): CartActionResult
    {
        $result = app(CreateCartLineAction::class)->execute($cart, $cartData);

        if ($result instanceof CartLine) {
            return CartActionResult::SUCCESS;
        }

        return CartActionResult::FAILED;
    }
}
