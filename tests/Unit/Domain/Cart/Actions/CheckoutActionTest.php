<?php

declare(strict_types=1);

use Domain\Cart\Actions\CheckoutAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertIsString;

beforeEach(function () {
    testInTenantContext();
});

it('can checkout cart lines', function () {
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

    $result = app(CheckoutAction::class)
        ->execute(CheckoutData::fromArray(['cart_line_ids' => $cartLineIds]));

    assertIsString($result);
    assertDatabaseHas(CartLine::class, [
        'checkout_reference' => $result,
    ]);
});
