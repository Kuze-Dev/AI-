<?php

declare(strict_types=1);

use App\Features\ECommerce\AllowGuestOrder;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\Models\CartLine;

use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext(AllowGuestOrder::class);

    $uuid = uuid_create(UUID_TYPE_RANDOM);

    $sessionId = time().$uuid;

    $cart = CartFactory::new()->setGuestId($sessionId)->createOne();

    CartLineFactory::new()->createOne();

    withHeader('Authorization', 'Bearer '.$sessionId);

    $this->cart = $cart;
});

it('can bulk delete cartlines', function () {
    $result = CartLineFactory::new()->times(3)
        ->afterCreating(function (CartLine $cartLine, $index) {
            $cartLine->purchasable_id = $index + 1;
            $cartLine->save();
        })->create();

    $cartLineIds = $result->pluck('uuid')->toArray();

    postJson('api/guest/carts/cartlines/bulk-remove', [
        'cart_line_ids' => $cartLineIds,
    ])
        ->assertValid()
        ->assertNoContent();
});
