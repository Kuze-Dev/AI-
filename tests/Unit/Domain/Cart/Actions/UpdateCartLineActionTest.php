<?php

declare(strict_types=1);

use Domain\Cart\Actions\UpdateCartLineAction;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\DataTransferObjects\UpdateCartLineData;
use Domain\Cart\Models\CartLine;

use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {
    testInTenantContext();
});

it('can update cart line quantity', function () {
    $cartLine = CartLineFactory::new()->createOne();

    $payload = [
        'type' => 'quantity',
        'quantity' => 3,
    ];

    $updatedCartLine = app(UpdateCartLineAction::class)
        ->execute($cartLine, UpdateCartLineData::fromArray($payload));

    assertInstanceOf(CartLine::class, $updatedCartLine);
    assertDatabaseHas(CartLine::class, [
        'quantity' => $updatedCartLine->quantity,
    ]);
});

it('can update cart line remarks', function () {
    $cartLine = CartLineFactory::new()->createOne();

    $payload = [
        'type' => 'remarks',
        'remarks' => [
            'notes' => 'test remarks',
        ],
    ];

    $updatedCartLine = app(UpdateCartLineAction::class)
        ->execute($cartLine, UpdateCartLineData::fromArray($payload));

    assertInstanceOf(CartLine::class, $updatedCartLine);
    assertDatabaseHas(CartLine::class, [
        'remarks' => json_encode([
            'notes' => 'test remarks',
        ]),
    ]);
});
