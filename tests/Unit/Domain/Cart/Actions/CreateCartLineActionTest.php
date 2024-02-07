<?php

declare(strict_types=1);

use Domain\Cart\Actions\CreateCartAction;
use Domain\Cart\Actions\CreateCartLineAction;
use Domain\Cart\DataTransferObjects\CreateCartData;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Product\Database\Factories\ProductVariantFactory;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {
    testInTenantContext();
});

it('can create cart lines with product as purchasable', function () {
    $customer = CustomerFactory::new()
        ->createOne();

    Sanctum::actingAs($customer);

    $cart = app(CreateCartAction::class)->execute($customer);

    $product = ProductFactory::new()
        ->createOne([
            'status' => true,
            'minimum_order_quantity' => 1,
        ]);

    $payload = [
        'purchasable_id' => $product->slug,
        'purchasable_type' => 'Product',
        'quantity' => 1,
    ];

    $cartline = app(CreateCartLineAction::class)
        ->execute($cart, CreateCartData::fromArray($payload));

    assertInstanceOf(CartLine::class, $cartline);
});

it('can create cart lines with product_variant as purchasable', function () {
    $customer = CustomerFactory::new()
        ->createOne();

    Sanctum::actingAs($customer);

    $cart = app(CreateCartAction::class)->execute($customer);

    $product = ProductFactory::new()
        ->createOne([
            'status' => true,
            'minimum_order_quantity' => 1,
        ]);

    $productVariant = ProductVariantFactory::new()
        ->for($product)
        ->createOne();

    $payload = [
        'purchasable_id' => $product->slug,
        'variant_id' => $productVariant->id,
        'purchasable_type' => 'Product',
        'quantity' => 1,
    ];

    $cartline = app(CreateCartLineAction::class)
        ->execute($cart, CreateCartData::fromArray($payload));

    assertInstanceOf(CartLine::class, $cartline);
});

it('can create cart lines with remarks', function () {
    $customer = CustomerFactory::new()
        ->createOne();

    Sanctum::actingAs($customer);

    $cart = app(CreateCartAction::class)->execute($customer);

    $product = ProductFactory::new()
        ->createOne([
            'status' => true,
            'minimum_order_quantity' => 1,
        ]);

    $payload = [
        'purchasable_id' => $product->slug,
        'purchasable_type' => 'Product',
        'quantity' => 1,
        'remarks' => [
            'notes' => 'test notes',
        ],
    ];

    $cartline = app(CreateCartLineAction::class)
        ->execute($cart, CreateCartData::fromArray($payload));

    assertInstanceOf(CartLine::class, $cartline);
    assertDatabaseHas(CartLine::class, [
        'remarks' => json_encode([
            'notes' => 'test notes',
        ]),
    ]);
});
