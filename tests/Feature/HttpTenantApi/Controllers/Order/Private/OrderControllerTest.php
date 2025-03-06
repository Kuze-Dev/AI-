<?php

declare(strict_types=1);

use App\Settings\OrderSettings;
use Domain\Address\Database\Factories\AddressFactory;
use Domain\Address\Database\Factories\CountryFactory;
use Domain\Address\Enums\AddressLabelAs;
use Domain\Cart\Actions\CheckoutAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Models\CartLine;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Order\Actions\PlaceOrderAction;
use Domain\Order\Database\Factories\OrderFactory;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\Events\OrderPlacedEvent;
use Domain\Order\Models\Order;
use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;
use Domain\Payments\Contracts\PaymentManagerInterface;
use Domain\Payments\Providers\OfflinePayment;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\Drivers\StorePickupDriver;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {
    testInTenantContext();

    app(OrderSettings::class)->fill(['email_sender_name' => fake()->safeEmail()])->save();

    CurrencyFactory::new()->createOne([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => '$',
        'enabled' => true,
    ]);

    $country = CountryFactory::new()->createOne([
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

    $address = AddressFactory::new()->createOne([
        'customer_id' => $customer->id,
        'state_id' => $state->id,
        'label_as' => AddressLabelAs::HOME,
        'address_line_1' => '185 Berry Street',
        'zip_code' => '94107',
        'city' => 'San Francisco',
        'is_default_shipping' => true,
        'is_default_billing' => true,
    ]);

    $shippingMethod = ShippingMethodFactory::new()->createOne(['title' => 'Store Pickup']);

    app(ShippingManagerInterface::class)->extend($shippingMethod->driver->value, fn () => new StorePickupDriver);

    $shippingMethod->update([
        'shipper_country_id' => $country->id,
        'shipper_state_id' => $state->id,
    ]);

    $paymentMethod = PaymentMethodFactory::new()->createOne(['title' => 'Cod']);

    app(PaymentManagerInterface::class)->extend($paymentMethod->slug, fn () => new OfflinePayment);

    Sanctum::actingAs($customer);

    ProductFactory::new()->times(3)->create([
        'status' => true,
        'minimum_order_quantity' => 1,
    ]);

    CartFactory::new()->setCustomerId($customer->id)->createOne();

    $cartLines = CartLineFactory::new()->times(3)
        ->afterCreating(function (CartLine $cartLine, $index) {
            $cartLine->purchasable_id = $index + 1;
            $cartLine->save();
        })->create();

    $this->country = $country;
    $this->state = $state;
    $this->customer = $customer;
    $this->address = $address;
    $this->shippingMethod = $shippingMethod;
    $this->paymentMethod = $paymentMethod;
    $this->cartLines = $cartLines;
});

it('can list orders', function () {
    $order = OrderFactory::new()
        ->createOne();

    getJson('api/orders')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($order) {
            $json
                ->count('data', 1)
                ->where('data.0.id', $order->getRouteKey())
                ->where('data.0.type', 'orders')
                ->etc();
        });
});

it('can list orders with include', function (string $include) {
    $order = OrderFactory::new()
        ->createOne();

    getJson('api/orders?'.http_build_query(['include' => $include]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($order) {
            $json
                ->count('data', 1)
                ->where('data.0.id', $order->getRouteKey())
                ->where('data.0.type', 'orders')
                ->has(
                    'included',
                    callback: fn (AssertableJson $json) => $json->where('type', 'orderLines')
                        ->where('id', (string) $order->orderLines[0]->id)
                        ->has('attributes')
                        ->etc()
                )
                ->etc();
        });
})->with([
    'orderLines',
]);

it('can store order', function () {
    $cartLineIds = $this->cartLines->pluck('uuid')->toArray();

    $reference = app(CheckoutAction::class)
        ->execute(CheckoutData::fromArray(['cart_line_ids' => $cartLineIds]));

    Event::fake(OrderPlacedEvent::class);

    postJson('api/orders', [
        'addresses' => [
            'shipping' => $this->address->id,
            'billing' => $this->address->id,
        ],
        'cart_reference' => $reference,
        'payment_method' => $this->paymentMethod->slug,
        'shipping_method' => $this->shippingMethod->slug,
    ])
        ->assertValid()
        ->assertOk();

    Event::assertDispatched(OrderPlacedEvent::class);
});

it('can show order', function () {
    $order = OrderFactory::new()
        ->createOne();

    getJson('api/orders/'.$order->getRouteKey())
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($order) {
            $json
                ->where('data.type', 'orders')
                ->where('data.id', $order->getRouteKey())
                ->where('data.attributes.reference', $order->getRouteKey())
                ->etc();
        });
});

it('can update order', function () {
    $cartLineIds = $this->cartLines->pluck('uuid')->toArray();

    $reference = app(CheckoutAction::class)
        ->execute(CheckoutData::fromArray(['cart_line_ids' => $cartLineIds]));

    $validatedData = [
        'addresses' => [
            'shipping' => $this->address->id,
            'billing' => $this->address->id,
        ],
        'cart_reference' => $reference,
        'payment_method' => $this->paymentMethod->slug,
        'shipping_method' => $this->shippingMethod->slug,
    ];

    $placeOrderData = PlaceOrderData::fromArray($validatedData);

    $result = app(PlaceOrderAction::class)
        ->execute($placeOrderData);

    $order = $result['order'];

    assertInstanceOf(Order::class, $order);

    patchJson('api/orders/'.$order->getRouteKey(), [
        'type' => 'status',
        'status' => 'cancelled',
        'notes' => 'test cancellation notes',
    ])
        ->assertValid()
        ->assertOk();

    assertDatabaseHas(Order::class, [
        'id' => $order->id,
        'status' => 'cancelled',
        'cancelled_reason' => 'test cancellation notes',
    ]);
});

it('can show order with includes', function (string $include) {
    $order = OrderFactory::new()
        ->createOne();

    getJson("api/orders/{$order->getRouteKey()}?".http_build_query(['include' => $include]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($order) {
            $json
                ->where('data.type', 'orders')
                ->where('data.id', $order->getRouteKey())
                ->where('data.attributes.reference', $order->getRouteKey())
                ->has(
                    'included',
                    callback: fn (AssertableJson $json) => $json->where('type', 'orderLines')
                        ->where('id', (string) $order->orderLines[0]->id)
                        ->has('attributes')
                        ->etc()
                )
                ->etc();
        });
})->with([
    'orderLines',
]);
