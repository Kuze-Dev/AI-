<?php

declare(strict_types=1);

use Domain\Cart\Actions\CheckoutAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext();

    $customer = CustomerFactory::new()
        ->createOne();

    ProductFactory::new()
        ->times(3)
        ->create([
            'status' => true,
            'minimum_order_quantity' => 1,
        ]);

    $cart = CartFactory::new()->setCustomerId($customer->id)->createOne();

    $cartLines = CartLineFactory::new()->times(3)
        ->afterCreating(function (CartLine $cartLine, $index) {
            $cartLine->purchasable_id = $index + 1;
            $cartLine->save();
        })->create();

    withHeader('Authorization', 'Bearer '.$customer
        ->createToken('testing-auth')
        ->plainTextToken);

    $this->cart = $cart;
    $this->cartLines = $cartLines;
});

it('can checkout', function () {
    $cartLineIds = $this->cartLines->pluck('uuid')->toArray();

    postJson('api/carts/checkouts', [
        'cart_line_ids' => $cartLineIds,
    ])
        ->assertValid()
        ->assertJsonStructure([
            'message',
            'reference',
        ])
        ->assertOk();
});

it('cant checkout when product inactive', function () {
    $inactiveProduct = ProductFactory::new()
        ->setStatus(false)
        ->createOne();

    $cartLine = CartLineFactory::new()
        ->setCartId($this->cart->id)
        ->setPurchasableId($inactiveProduct->id)
        ->createOne();

    postJson('api/carts/checkouts', [
        'cart_line_ids' => [$cartLine->uuid],
    ])->assertUnprocessable();
});

it('cant checkout when product didnt meet the minimum order quantity', function () {
    $inactiveProduct = ProductFactory::new()
        ->setMinimumOrderQuantity(5)
        ->createOne();

    $cartLine = CartLineFactory::new()
        ->setCartId($this->cart->id)
        ->setPurchasableId($inactiveProduct->id)
        ->createOne();

    postJson('api/carts/checkouts', [
        'cart_line_ids' => [$cartLine->uuid],
    ])->assertUnprocessable();
});

it('can show checkout items', function () {
    $cartLineIds = $this->cartLines->pluck('uuid')->toArray();

    $result = app(CheckoutAction::class)
        ->execute(CheckoutData::fromArray(['cart_line_ids' => $cartLineIds]));

    getJson('api/carts/checkouts?'.http_build_query(['reference' => $result]))
        ->assertValid()
        ->assertJsonCount(3, 'data')
        ->assertJson(function (AssertableJson $json) use ($cartLineIds) {
            $json
                ->where('data.0.id', $cartLineIds[0])
                ->where('data.0.type', 'cartLines')
                ->where('data.0.attributes.id', $cartLineIds[0])
                ->where('data.1.id', $cartLineIds[1])
                ->where('data.1.type', 'cartLines')
                ->where('data.1.attributes.id', $cartLineIds[1])
                ->where('data.2.id', $cartLineIds[2])
                ->where('data.2.type', 'cartLines')
                ->where('data.2.attributes.id', $cartLineIds[2])
                ->etc();
        })
        ->assertOk();
});
