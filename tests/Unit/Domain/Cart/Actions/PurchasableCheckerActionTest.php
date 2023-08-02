<?php

declare(strict_types=1);

use Domain\Cart\Actions\PurchasableCheckerAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Laravel\Sanctum\Sanctum;

use function PHPUnit\Framework\assertCount;

beforeEach(function () {
    testInTenantContext();
});

it('can check cartLine that belongsTo current auth', function () {

    $customer = CustomerFactory::new()
        ->createOne();

    Sanctum::actingAs($customer);

    ProductFactory::new()->times(3)->create();

    CartFactory::new()->setCustomerId($customer->id)->createOne();

    $result = CartLineFactory::new()->times(3)
        ->afterCreating(function (CartLine $cartLine, $index) {
            $cartLine->purchasable_id = $index + 1;
            $cartLine->save();
        })->create();

    $cartLineIds = $result->pluck('uuid')->toArray();

    $checkAuth = app(PurchasableCheckerAction::class)->checkAuth($cartLineIds);

    assertCount($checkAuth, $cartLineIds);
});

it('can check purchasable stocks', function () {
    $customer = CustomerFactory::new()
        ->createOne();

    Sanctum::actingAs($customer);

    ProductFactory::new()->times(3)->create();

    CartFactory::new()->setCustomerId($customer->id)->createOne();

    $result = CartLineFactory::new()->times(3)
        ->afterCreating(function (CartLine $cartLine, $index) {
            $cartLine->purchasable_id = $index + 1;
            $cartLine->save();
        })->create();

    $cartLineIds = $result->pluck('uuid')->toArray();

    $checkStocks = app(PurchasableCheckerAction::class)->checkStock($cartLineIds);

    assertCount($checkStocks, $cartLineIds);
});
