<?php

declare(strict_types=1);

use App\Features\ECommerce\AllowGuestOrder;
use App\Settings\OrderSettings;
use Domain\Address\Database\Factories\CountryFactory;
use Domain\Cart\Actions\CheckoutAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Models\CartLine;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Order\Actions\PublicOrder\GuestPlaceOrderAction;
use Domain\Order\Database\Factories\OrderFactory;
use Domain\Order\DataTransferObjects\GuestPlaceOrderData;
use Domain\Order\Events\PublicOrder\GuestOrderPlacedEvent;
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

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withHeader;
use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {
    testInTenantContext(AllowGuestOrder::class);

    OrderSettings::fake(['email_sender_name' => fake()->safeEmail()]);

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

    $shippingMethod = ShippingMethodFactory::new()->createOne(['title' => 'Store Pickup']);

    app(ShippingManagerInterface::class)->extend($shippingMethod->driver->value, fn () => new StorePickupDriver());

    $shippingMethod->update([
        'shipper_country_id' => $country->id,
        'shipper_state_id' => $state->id,
    ]);

    $paymentMethod = PaymentMethodFactory::new()->createOne(['title' => 'Cod']);

    app(PaymentManagerInterface::class)->extend($paymentMethod->slug, fn () => new OfflinePayment());

    ProductFactory::new()->times(3)->create([
        'status' => true,
        'minimum_order_quantity' => 1,
        'allow_guest_purchase' => true,
    ]);

    $uuid = uuid_create(UUID_TYPE_RANDOM);

    $sessionId = time().$uuid;

    CartFactory::new()->setGuestId($sessionId)->createOne();

    $cartLines = CartLineFactory::new()->times(3)
        ->afterCreating(function (CartLine $cartLine, $index) {
            $cartLine->purchasable_id = $index + 1;
            $cartLine->save();
        })->create();

    withHeader('Authorization', 'Bearer '.$sessionId);

    $this->country = $country;
    $this->state = $state;
    $this->shippingMethod = $shippingMethod;
    $this->paymentMethod = $paymentMethod;
    $this->cartLines = $cartLines;

    $this->customer = [
        'first_name' => 'Benedict',
        'last_name' => 'Regore',
        'mobile' => '09208024445',
        'email' => 'benedict.halcyondigital@gmail.com',
    ];

    $this->shippingAddress = [
        'country_id' => 'US',
        'state_id' => $state->id,
        'address_line_1' => '185 Berry Street',
        'zip_code' => '94107',
        'city' => 'San Francisco',
        'label_as' => 'office',
    ];
});

it('can store guest order', function () {
    $cartLineIds = $this->cartLines->pluck('uuid')->toArray();

    $reference = app(CheckoutAction::class)
        ->execute(CheckoutData::fromArray(['cart_line_ids' => $cartLineIds]));

    Event::fake(GuestOrderPlacedEvent::class);

    postJson('api/guest/orders', [
        'cart_reference' => $reference,
        'customer' => $this->customer,
        'addresses' => [
            'billing' => $this->shippingAddress,
            'shipping' => $this->shippingAddress,
        ],
        'payment_method' => $this->paymentMethod->slug,
        'shipping_method' => $this->shippingMethod->slug,
    ])
        ->assertValid()
        ->assertOk();

    Event::assertDispatched(GuestOrderPlacedEvent::class);
});

it('can show order', function () {
    $order = OrderFactory::new()
        ->createOne();

    getJson('api/guest/orders/'.$order->getRouteKey())
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
        'customer' => $this->customer,
        'addresses' => [
            'billing' => $this->shippingAddress,
            'shipping' => $this->shippingAddress,
        ],
        'cart_reference' => $reference,
        'payment_method' => $this->paymentMethod->slug,
        'shipping_method' => $this->shippingMethod->slug,
    ];

    $placeOrderData = GuestPlaceOrderData::fromArray($validatedData);

    $result = app(GuestPlaceOrderAction::class)
        ->execute($placeOrderData);

    $order = $result['order'];

    assertInstanceOf(Order::class, $order);

    patchJson('api/guest/orders/'.$order->getRouteKey(), [
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

    getJson("api/guest/orders/{$order->getRouteKey()}?".http_build_query(['include' => $include]))
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
