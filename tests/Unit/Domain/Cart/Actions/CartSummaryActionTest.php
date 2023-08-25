<?php

declare(strict_types=1);

use Domain\Cart\Actions\CartSummaryAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    testInTenantContext();

    $customer = CustomerFactory::new()
        ->createOne();

    Sanctum::actingAs($customer);

    ProductFactory::new()->times(3)->create();

    CartFactory::new()->setCustomerId($customer->id)->createOne();

    $cartLines = CartLineFactory::new()->times(3)
        ->afterCreating(function (CartLine $cartLine, $index) {
            $cartLine->purchasable_id = $index + 1;
            $cartLine->save();
        })->create();

    $this->cartLines = $cartLines;
    $this->customer = $customer;

    return compact('cartLines', 'customer');
});

it('can get subtotal', function () {
    $subtotal = app(CartSummaryAction::class)->getSubTotal($this->cartLines);

    expect($subtotal)->toBeFloat();
});
