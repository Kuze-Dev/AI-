<?php

declare(strict_types=1);

use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Product\Database\Factories\ProductVariantFactory;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext();

    $product = ProductFactory::new()->createOne();

    $product->update([
        'allow_customer_remarks' => true,
    ]);

    $customer = CustomerFactory::new()
        ->createOne();

    CartFactory::new()->setCustomerId($customer->id)->createOne();

    withHeader('Authorization', 'Bearer ' . $customer
        ->createToken('testing-auth')
        ->plainTextToken);

    $this->product = $product;

    return compact('product');
});

it('can add to cart a purchasable product', function () {
    postJson('api/carts/cartlines', [
        'purchasable_id' => $this->product->slug,
        'purchasable_type' => 'Product',
        'quantity' => 1,
    ])
        ->assertValid()
        ->assertOk();
});

it('can add to cart a purchasable product with variant', function () {
    $productVariant = ProductVariantFactory::new()->setProductId($this->product->id)
        ->createOne();

    postJson('api/carts/cartlines', [
        'purchasable_id' => $this->product->slug,
        'variant_id' => $productVariant->id,
        'purchasable_type' => 'Product',
        'quantity' => 1,
    ])
        ->assertValid()
        ->assertOk();
});

it('can add to cart a purchasable product with remarks', function () {
    $productVariant = ProductVariantFactory::new()->setProductId($this->product->id)
        ->createOne();

    postJson('api/carts/cartlines', [
        'purchasable_id' => $this->product->slug,
        'variant_id' => $productVariant->id,
        'purchasable_type' => 'Product',
        'quantity' => 1,
        'remarks' => [
            'notes' => 'test remarks',
        ],
    ])
        ->assertValid()
        ->assertOk();
});

it('can update cart line quantity', function () {
    $cartLine = CartLineFactory::new()->createOne();

    patchJson('api/carts/cartlines/' . $cartLine->uuid, [
        'type' => 'quantity',
        'quantity' => 2,
    ])
        ->assertValid()
        ->assertOk();
});

it('can update cart line remarks', function () {

    $cartLine = CartLineFactory::new()->createOne();

    patchJson('api/carts/cartlines/' . $cartLine->uuid, [
        'type' => 'remarks',
        'remarks' => [
            'notes' => 'test remarks',
        ],
    ])
        ->assertValid()
        ->assertOk();
});

it('can delete cart line', function () {

    $cartLine = CartLineFactory::new()->createOne();

    deleteJson('api/carts/cartlines/' . $cartLine->uuid)
        ->assertValid()
        ->assertNoContent();
});
