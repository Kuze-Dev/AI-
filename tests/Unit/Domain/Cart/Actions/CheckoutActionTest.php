<?php

declare(strict_types=1);

use Domain\Cart\Database\Factories\CartFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    testInTenantContext();
});

it('can checkout cart lines', function () {
    // $customer = CustomerFactory::new()
    //     ->createOne();

    // Sanctum::actingAs($customer);

    // ProductFactory::new()->times(3)->create();

    // CartFactory::new()->setCustomerId($customer->id)->createOne();

    // CartLineFactory::new()->times(3)
    //     ->afterCreating(function (CartLine $cartLine, $index) {
    //         $cartLine->purchasable_id = $index + 1;
    //         $cartLine->save();
    //     })->create();

    // $cartLineIds = [1, 2];

    // $result = app(BulkDestroyCartLineAction::class)
    //     ->execute($cartLineIds);

    // expect($result)->toBe(true);
    // assertEquals(1, CartLine::where('id', 3)->count());
});
