<?php

declare(strict_types=1);

use App\Features\ECommerce\AllowGuestOrder;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Product\Database\Factories\ProductVariantFactory;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext(AllowGuestOrder::class);

    $product = ProductFactory::new()
        ->createOne([
            'status' => true,
            'minimum_order_quantity' => 1,
            'allow_customer_remarks' => true,
            'allow_guest_purchase' => true,
        ]);

    $uuid = uuid_create(UUID_TYPE_RANDOM);

    $sessionId = time().$uuid;

    CartFactory::new()->setGuestId($sessionId)->createOne();

    withHeader('Authorization', 'Bearer '.$sessionId);

    $this->product = $product;
});

it('can add to cart a purchasable product', function () {
    postJson('api/guest/carts/cartlines', [
        'purchasable_id' => $this->product->slug,
        'purchasable_type' => 'Product',
        'quantity' => 1,
    ])
        ->assertValid()
        ->assertOk();
});

it('cant add to cart when purchasable cant purchase as a guest', function () {
    $invalidProduct = ProductFactory::new()
        ->createOne([
            'status' => true,
            'minimum_order_quantity' => 1,
            'allow_guest_purchase' => false,
        ]);

    postJson('api/guest/carts/cartlines', [
        'purchasable_id' => $invalidProduct->slug,
        'purchasable_type' => 'Product',
        'quantity' => 1,
    ])->assertUnprocessable();
});

it('can add to cart a purchasable product with variant', function () {
    $productVariant = ProductVariantFactory::new()
        ->for($this->product)
        ->createOne();

    postJson('api/guest/carts/cartlines', [
        'purchasable_id' => $this->product->slug,
        'variant_id' => $productVariant->id,
        'purchasable_type' => 'Product',
        'quantity' => 1,
    ])
        ->assertValid()
        ->assertOk();
});

it('can add to cart a purchasable product with remarks', function () {
    $productVariant = ProductVariantFactory::new()
        ->for($this->product)
        ->createOne();

    postJson('api/guest/carts/cartlines', [
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
    $product = ProductFactory::new()
        ->createOne([
            'status' => true,
            'minimum_order_quantity' => 1,
            'allow_customer_remarks' => true,
            'allow_guest_purchase' => true,
            'stock' => 5,
        ]);

    postJson('api/guest/carts/cartlines', [
        'purchasable_id' => $product->slug,
        'purchasable_type' => 'Product',
        'quantity' => 1,
    ])
        ->assertValid()
        ->assertOk();

    $cartLine = CartLineFactory::new()->setPurchasableId($product->id)->createOne();

    patchJson('api/guest/carts/cartlines/'.$cartLine->uuid, [
        'type' => 'quantity',
        'quantity' => 2,
    ])
        ->assertValid()
        ->assertOk();
});

it('can update cart line remarks', function () {

    $cartLine = CartLineFactory::new()->createOne();

    patchJson('api/guest/carts/cartlines/'.$cartLine->uuid, [
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

    deleteJson('api/guest/carts/cartlines/'.$cartLine->uuid)
        ->assertValid()
        ->assertNoContent();
});
