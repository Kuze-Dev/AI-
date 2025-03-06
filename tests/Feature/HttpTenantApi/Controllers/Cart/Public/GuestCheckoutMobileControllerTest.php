<?php

declare(strict_types=1);

use App\Features\ECommerce\AllowGuestOrder;
use Domain\Address\Models\Country;
use Domain\Cart\Actions\CheckoutAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Models\CartLine;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\Drivers\StorePickupDriver;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;

use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext(AllowGuestOrder::class);

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

    app(ShippingManagerInterface::class)->extend($shippingMethod->driver->value, fn () => new StorePickupDriver);

    ProductFactory::new()
        ->times(3)
        ->create([
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
    $this->cartLines = $cartLines;
    $this->shippingMethod = $shippingMethod;

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

it('can get guest checkout summary in mobile version', function () {

    $cartLineIds = $this->cartLines->pluck('uuid')->toArray();

    $reference = app(CheckoutAction::class)
        ->execute(CheckoutData::fromArray(['cart_line_ids' => $cartLineIds]));

    postJson(
        'api/v2/guest/carts/summary',
        [
            'reference' => $reference,
            'customer' => $this->customer,
            'billing_address' => $this->billingAddress,
            'shipping_address' => $this->shippingAddress,
            'shipping_method_id' => $this->shippingMethod->slug,
        ]
    )
        ->assertValid()
        ->assertJsonStructure([
            'summary' => [
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
            ],
            'cartLines',
            'reference',
        ])
        ->assertOk();
});
