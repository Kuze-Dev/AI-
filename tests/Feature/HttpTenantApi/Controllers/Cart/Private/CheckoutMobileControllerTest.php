<?php

declare(strict_types=1);

use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Cart\Actions\CheckoutAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\Drivers\StorePickupDriver;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;

use function Pest\Laravel\getJson;
use function Pest\Laravel\withHeader;

beforeEach(function () {
    testInTenantContext();

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

    $customer = CustomerFactory::new()
        ->createOne();

    $address = Address::create([
        'customer_id' => $customer->id,
        'state_id' => $state->id,
        'label_as' => AddressLabelAs::HOME,
        'address_line_1' => '185 Berry Street',
        'zip_code' => '94107',
        'city' => 'San Francisco',
        'is_default_shipping' => true,
        'is_default_billing' => true,
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
        ]);

    $cart = CartFactory::new()->setCustomerId($customer->id)->createOne();

    $cartLines = CartLineFactory::new()->times(3)
        ->afterCreating(function (CartLine $cartLine, $index) {
            $cartLine->purchasable_id = $index + 1;
            $cartLine->save();
        })->create();

    withHeader('Authorization', 'Bearer '.$customer
        ->createToken('testing-auth')
        ->plainTextToken);

    $this->cart = $cart;
    $this->cartLines = $cartLines;
    $this->address = $address;
    $this->shippingMethod = $shippingMethod;
});

it('can get checkout summary in mobile version', function () {

    $cartLineIds = $this->cartLines->pluck('uuid')->toArray();

    $reference = app(CheckoutAction::class)
        ->execute(CheckoutData::fromArray(['cart_line_ids' => $cartLineIds]));

    getJson('api/v2/carts/summary?'.http_build_query(
        [
            'reference' => $reference,
            'billing_address_id' => $this->address->id,
            'shipping_address_id' => $this->address->id,
            'shipping_method_id' => $this->shippingMethod->slug,
        ]
    ))
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
