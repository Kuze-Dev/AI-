<?php

declare(strict_types=1);

use App\Features\ECommerce\AllowGuestOrder;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext(AllowGuestOrder::class);

    ProductFactory::new()
        ->createOne([
            'status' => true,
            'minimum_order_quantity' => 1,
            'allow_guest_purchase' => true,
        ]);

    $uuid = uuid_create(UUID_TYPE_RANDOM);

    $sessionId = time().$uuid;

    $cart = CartFactory::new()->setGuestId($sessionId)->createOne();

    withHeader('Authorization', 'Bearer '.$sessionId);

    $this->cart = $cart;
});

it('can show cart', function () {
    getJson('api/guest/carts')
        ->assertValid()
        ->assertJson([
            'data' => [
                'id' => (string) $this->cart->uuid,
                'type' => 'carts',
                'attributes' => [
                    'id' => $this->cart->uuid,
                    'coupon_code' => null,
                ],
            ],
            'jsonapi' => [
                'version' => '1.0',
            ],
        ])
        ->assertOk();
});

it('can show cart with includes', function (string $include) {
    $cart = $this->cart;

    $cartLine = CartLineFactory::new()->createOne();

    getJson('api/guest/carts?'.http_build_query(['include' => $include]))
        ->assertValid()
        ->assertJson(function (AssertableJson $json) use ($cart, $cartLine) {
            $json
                ->where('data.type', 'carts')
                ->where('data.id', $cart->uuid)
                ->where('data.attributes.id', $cart->uuid)
                ->has('included', 1)
                ->has(
                    'included',
                    callback: function (AssertableJson $json) use ($cartLine) {
                        $json->where('type', 'cartLines')
                            ->where('id', $cartLine->uuid)
                            ->has('attributes')
                            ->etc();
                    }
                )
                ->etc();
        })
        ->assertOk();
})->with(['cartLines.media']);

it('can delete cart', function () {
    deleteJson('api/guest/carts/'.$this->cart->uuid)
        ->assertValid()
        ->assertNoContent();
});
