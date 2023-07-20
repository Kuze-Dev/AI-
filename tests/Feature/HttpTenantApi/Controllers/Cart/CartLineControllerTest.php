<?php

declare(strict_types=1);

use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Product\Models\Product;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext();

    ProductFactory::new()->createOne();

    $customer = CustomerFactory::new()
        ->createOne();

    CartFactory::new()->setCustomerId($customer->id)->createOne();

    withHeader('Authorization', 'Bearer ' . $customer
        ->createToken('testing-auth')
        ->plainTextToken);
});

it('can add to cart', function () {
    $product = ProductFactory::new()
        ->createOne();

    postJson('api/carts/cartlines', [
        'purchasable_id' => $product->id,
        'purchasable_type' => "Product",
        "quantity" => 1
    ])
        ->assertValid()
        ->assertOk();
});

it('can update cart line quantity', function () {

    $cartLine = CartLineFactory::new()->createOne();

    patchJson('api/carts/cartlines/' . $cartLine->id, [
        'type' => "quantity",
        "quantity" => 2
    ])
        ->assertValid()
        ->assertOk();
});

it('can delete cart line', function () {

    $cartLine = CartLineFactory::new()->createOne();

    deleteJson('api/carts/cartlines/' . $cartLine->id)
        ->assertValid()
        ->assertNoContent();
});
