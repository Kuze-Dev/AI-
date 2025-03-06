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
use Domain\Order\Actions\PrepareOrderAction;
use Domain\Order\Actions\SplitOrderAction;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\Events\OrderPlacedEvent;
use Domain\Order\Notifications\AdminOrderPlacedMail;
use Domain\Order\Notifications\OrderPlacedMail;
use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;
use Domain\Payments\Contracts\PaymentManagerInterface;
use Domain\Payments\Providers\OfflinePayment;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\Drivers\StorePickupDriver;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;

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

    $cartLineIds = $cartLines->pluck('uuid')->toArray();

    $reference = app(CheckoutAction::class)
        ->execute(CheckoutData::fromArray(['cart_line_ids' => $cartLineIds]));

    $validatedData = [
        'addresses' => [
            'shipping' => $address->id,
            'billing' => $address->id,
        ],
        'cart_reference' => $reference,
        'payment_method' => $paymentMethod->slug,
        'shipping_method' => $shippingMethod->slug,
    ];

    $placeOrderData = PlaceOrderData::fromArray($validatedData);
    $preparedOrder = app(PrepareOrderAction::class)
        ->execute($placeOrderData);
    $splittedOrder = app(SplitOrderAction::class)->execute($preparedOrder, $placeOrderData);
    $order = $splittedOrder['order'];

    $this->order = $order;
    $this->customer = $customer;
    $this->placeOrderData = $placeOrderData;
    $this->preparedOrder = $preparedOrder;
});

it('can send order email notification to admin', function () {
    $orderSettings = app(OrderSettings::class)->fill([
        'admin_should_receive' => true,
        'admin_main_receiver' => fake()->safeEmail(),
    ])->save();

    Notification::fake();

    $event = new OrderPlacedEvent($this->order, $this->preparedOrder, $this->placeOrderData);
    event($event);

    Notification::assertSentOnDemand(
        AdminOrderPlacedMail::class,
        fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === $orderSettings->admin_main_receiver
    );
});

it('cannot send order email notification to admin', function () {
    $orderSettings = app(OrderSettings::class)->fill([
        'admin_should_receive' => false,
        'admin_main_receiver' => fake()->safeEmail(),
    ])->save();

    Notification::fake();

    $event = new OrderPlacedEvent($this->order, $this->preparedOrder, $this->placeOrderData);
    event($event);

    Notification::assertNotSentTo(
        new AnonymousNotifiable,
        AdminOrderPlacedMail::class,
        fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === $orderSettings->admin_main_receiver
    );
});

it('can send order email notification to customer', function () {
    Notification::fake();

    $event = new OrderPlacedEvent($this->order, $this->preparedOrder, $this->placeOrderData);
    event($event);

    $customer = $this->customer;
    Notification::assertSentTo(
        $customer,
        OrderPlacedMail::class,
    );
});
