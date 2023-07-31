<?php

declare(strict_types=1);

use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Product\Database\Factories\ProductFactory;

use function Pest\Laravel\getJson;
use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext();

    $customer = CustomerFactory::new()
        ->createOne();

    ProductFactory::new()->times(3)->create();

    $cart = CartFactory::new()->setCustomerId($customer->id)->createOne();

    $cartLines = CartLineFactory::new()->times(3)
        ->afterCreating(function (CartLine $cartLine, $index) {
            $cartLine->purchasable_id = $index + 1;
            $cartLine->save();
        })->create();


    withHeader('Authorization', 'Bearer ' . $customer
        ->createToken('testing-auth')
        ->plainTextToken);

    $this->cart = $cart;
    $this->cartLines = $cartLines;

    return compact('cart', 'cartLines');
});

it('can get cart count', function () {
    getjson('api/carts/count')
        ->assertValid()
        ->assertJson([
            'cartCount' => 3,
        ])
        ->assertOk();
});

it('can show cart summary', function () {
    $cartLineIds = $this->cartLines->pluck('uuid')->toArray();

    $commaSeparatedIds = implode(',', $cartLineIds);

    getJson('api/carts/summary?' . http_build_query(['cart_line_ids' => $commaSeparatedIds]))
        ->assertValid()
        ->assertJsonStructure([
            'tax' => [
                'inclusive_sub_total',
                'display',
                'percentage',
                'amount',
            ],
            'discount' => [
                'status',
                'message',
                'type',
                'amount',
                'discount_type',
                'total_savings',
            ],
            'sub_total',
            'shipping_fee',
            'total',
        ])
        ->assertOk();
});
