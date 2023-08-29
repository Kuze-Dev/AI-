<?php

declare(strict_types=1);

use Domain\Cart\Actions\BulkDestroyCartLineAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Laravel\Sanctum\Sanctum;

use function PHPUnit\Framework\assertEquals;

beforeEach(function () {
    testInTenantContext();
});

it('can bulk delete cart lines', function () {
    $customer = CustomerFactory::new()
        ->createOne();

    Sanctum::actingAs($customer);

    ProductFactory::new()->times(3)->create([
        'status' => true,
        'minimum_order_quantity' => 1,
    ]);

    CartFactory::new()->setCustomerId($customer->id)->createOne();

    $result = CartLineFactory::new()->times(3)
        ->afterCreating(function (CartLine $cartLine, $index) {
            $cartLine->purchasable_id = $index + 1;
            $cartLine->save();
        })->create();

    $cartLineIds = $result->pluck('uuid')->toArray();

    $result = app(BulkDestroyCartLineAction::class)
        ->execute(array_slice($cartLineIds, 0, 2));

    expect($result)->toBe(true);
    assertEquals(1, CartLine::where('id', 3)->count());
});
