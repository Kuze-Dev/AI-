<?php

declare(strict_types=1);

use App\Features\Customer\AddressBase;
use App\Features\Customer\CustomerBase;
use App\Features\Customer\TierBase;
use App\Features\ECommerce\AllowGuestOrder;
use App\Features\ECommerce\ECommerceBase;
use App\Features\Shopconfiguration\Shipping\ShippingStorePickup;
use Domain\Address\Models\Country;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\Models\CartLine;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\Drivers\StorePickupDriver;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext([
        CustomerBase::class,
        TierBase::class,
        AddressBase::class,
        ECommerceBase::class,
        ShippingStorePickup::class,
        AllowGuestOrder::class,
    ]);

    $country = Country::create([
        'code' => 'US',
        'name' => 'United States',
        'capital' => 'Washington',
        'timezone' => 'UTC-10:00',
        'active' => true,
    ]);

    $state = $country->states()->create([
        'name' => 'CA',
        'code' => 'BH',
    ]);

    $shippingMethod = ShippingMethodFactory::new()->createOne(
        [
            'title' => 'Store Pickup',
            'shipper_country_id' => $country->id,
            'shipper_state_id' => $state->id,
        ]
    );

    app(ShippingManagerInterface::class)->extend($shippingMethod->driver->value, fn () => new StorePickupDriver());

    ProductFactory::new()->times(3)->create([
        'status' => true,
        'minimum_order_quantity' => 1,
        'allow_guest_purchase' => true,
    ]);

    $uuid = uuid_create(UUID_TYPE_RANDOM);

    $sessionId = time().$uuid;

    $cart = CartFactory::new()->setGuestId($sessionId)->createOne();

    $cartLines = CartLineFactory::new()->times(3)
        ->afterCreating(function (CartLine $cartLine, $index) {
            $cartLine->purchasable_id = $index + 1;
            $cartLine->save();
        })->create();

    withHeader('Authorization', 'Bearer '.$sessionId);

    $this->cart = $cart;
    $this->shippingMethod = $shippingMethod;
    $this->cartLines = $cartLines;

    $this->customer = [
        'first_name' => 'Benedict',
        'last_name' => 'Regore',
        'mobile' => '09208024445',
        'email' => 'benedict.halcyondigital@gmail.com',
    ];

    $this->billingAddress = [
        'country_id' => 'PH',
        'state_id' => 3220,
        'address_line_1' => '855 Proper',
        'zip_code' => '1091',
        'city' => 'San Luis',
    ];

    $this->shippingAddress = [
        'country_id' => 'US',
        'state_id' => $state->id,
        'address_line_1' => '185 Berry Street',
        'zip_code' => '94107',
        'city' => 'San Francisco',
    ];
});

it('can get cart count', function () {
    getjson('api/guest/carts/count')
        ->assertValid()
        ->assertJson([
            'cartCount' => 3,
        ])
        ->assertOk();
});

it('can show cart summary', function () {
    $cartLineIds = $this->cartLines->pluck('uuid')->toArray();

    postJson(
        'api/guest/carts/summary',
        [
            'cart_line_ids' => $cartLineIds,
            'customer' => $this->customer,
            'billing_address' => $this->billingAddress,
            'shipping_address' => $this->shippingAddress,
            'shipping_method_id' => $this->shippingMethod->slug,
        ]
    )
        ->assertValid()
        ->assertJsonStructure([
            'tax' => [
                'display',
                'percentage',
                'amount',
            ],
            'sub_total' => [
                'initial_amount',
                'discounted_amount',
            ],
            'shipping_fee' => [
                'initial_amount',
                'discounted_amount',
            ],
            'total',
        ])
        ->assertOk();
});
