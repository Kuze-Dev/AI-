<?php

declare(strict_types=1);

use Domain\Cart\Actions\DestroyCartLineAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    testInTenantContext();
});

it('can delete cart line', function () {
    $customer = CustomerFactory::new()
        ->createOne();

    Sanctum::actingAs($customer);

    CartFactory::new()->setCustomerId($customer->id)->createOne();

    $cartLine = CartLineFactory::new()->createOne();

    $result = app(DestroyCartLineAction::class)
        ->execute($cartLine);

    expect($result)->toBe(true);
});
