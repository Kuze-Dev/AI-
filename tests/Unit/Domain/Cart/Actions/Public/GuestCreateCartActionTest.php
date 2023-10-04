<?php

declare(strict_types=1);

use Domain\Cart\Actions\PublicCart\GuestCreateCartAction;
use Domain\Cart\Models\Cart;

use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {
    testInTenantContext();
});

it('can create guest cart', function () {

    $cart = app(GuestCreateCartAction::class)->execute(null);

    assertInstanceOf(Cart::class, $cart);
});
