<?php

declare(strict_types=1);

use Domain\Cart\Actions\CreateCartAction;
use Domain\Cart\Models\Cart;
use Domain\Customer\Database\Factories\CustomerFactory;
use Laravel\Sanctum\Sanctum;

use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {
    testInTenantContext();
});

it('can create cart', function () {
    $customer = CustomerFactory::new()
        ->createOne();

    Sanctum::actingAs($customer);

    $cart = app(CreateCartAction::class)->execute($customer);

    assertInstanceOf(Cart::class, $cart);
});
