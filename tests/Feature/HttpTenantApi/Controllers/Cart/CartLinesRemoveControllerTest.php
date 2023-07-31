<?php

declare(strict_types=1);

use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Database\Factories\CustomerFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext();
    $customer = CustomerFactory::new()
        ->createOne();

    $cart = CartFactory::new()->setCustomerId($customer->id)->createOne();

    $cartLine = CartLineFactory::new()->createOne();

    withHeader('Authorization', 'Bearer ' . $customer
        ->createToken('testing-auth')
        ->plainTextToken);

    $this->cart = $cart;

    return compact('cart');
});

it('can bulk delete cartlines', function () {
    $result = CartLineFactory::new()->times(3)
        ->afterCreating(function (CartLine $cartLine, $index) {
            $cartLine->purchasable_id = $index + 1;
            $cartLine->save();
        })->create();

    $cartLineIds = $result->pluck('uuid')->toArray();

    postJson('api/carts/cartlines/bulk-remove', [
        'cart_line_ids' => $cartLineIds,
    ])
        ->assertValid()
        ->assertNoContent();
});
