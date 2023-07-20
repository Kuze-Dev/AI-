<?php

declare(strict_types=1);

use Domain\Cart\Actions\DestroyCartAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    testInTenantContext();
});

it('can delete cart', function () {
    $customer = CustomerFactory::new()
        ->createOne();

    Sanctum::actingAs($customer);

    $cart = CartFactory::new()->setCustomerId($customer->id)->createOne();
});
