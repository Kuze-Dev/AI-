<?php

declare(strict_types=1);

use App\Settings\OrderSettings;
use Domain\Address\Database\Factories\AddressFactory;
use Domain\Address\Database\Factories\CountryFactory;
use Domain\Address\Database\Factories\StateFactory;
use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\Address;
use Domain\Cart\Actions\CheckoutAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Models\CartLine;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Currency\Models\Currency;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Order\Actions\PrepareOrderAction;
use Domain\Order\DataTransferObjects\PlaceOrderData;
use Domain\Order\DataTransferObjects\PreparedOrderData;
use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Contracts\PaymentManagerInterface;
use Domain\Payments\Providers\OfflinePayment;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\Drivers\StorePickupDriver;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Domain\ShippingMethod\Models\ShippingMethod;
use Laravel\Sanctum\Sanctum;

use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {
    testInTenantContext();

    OrderSettings::fake([
        'email_sender_name' => fake()->safeEmail(),
    ]);

    CurrencyFactory::new()->createOne([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => '$',
        'enabled' => true,
    ]);

    $country = CountryFactory::new()
        ->has(StateFactory::new([
            'name' => 'CA',
            'code' => 'BH',
        ]))
        ->createOne([
            'code' => 'US',
            'name' => 'United States',
            'capital' => 'Washington',
            'timezone' => 'UTC-10:00',
            'active' => true,
        ]);

    $state = $country->states[0];

    $customer = CustomerFactory::new()
        ->createOne();

    $address = AddressFactory::new()
        ->for($customer)
        ->for($state)
        ->createOne([
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

    $this->country = $country;
    $this->state = $state;
    $this->customer = $customer;
    $this->address = $address;
    $this->shippingMethod = $shippingMethod;
    $this->paymentMethod = $paymentMethod;
    $this->cartLines = $cartLines;
    $this->placeOrderData = $placeOrderData;
});

it('can prepare address', function () {
    $addresses = app(PrepareOrderAction::class)->prepareAddress($this->placeOrderData);

    expect($addresses)->toHaveKey('shippingAddress');
    expect($addresses)->toHaveKey('billingAddress');

    $shippingAddress = $addresses['shippingAddress'];
    $billingAddress = $addresses['billingAddress'];

    assertInstanceOf(Address::class, $shippingAddress);
    assertInstanceOf(Address::class, $billingAddress);
});

it('can prepare currency', function () {

    $currency = app(PrepareOrderAction::class)->prepareCurrency();

    assertInstanceOf(Currency::class, $currency);
});

it('can prepare cartlines', function () {
    $cartLines = app(PrepareOrderAction::class)->prepareCartLines($this->placeOrderData);

    $cartLines->each(function ($item) {
        expect($item)->toBeInstanceOf(CartLine::class);
    });
});

it('can prepare tax', function () {
    $taxZone = app(PrepareOrderAction::class)->prepareTax($this->placeOrderData);

    expect($taxZone)->toBe(null);
});

it('can prepare discount', function () {
    $discount = app(PrepareOrderAction::class)->prepareDiscount($this->placeOrderData);

    expect($discount)->toBe(null);
});

it('can prepare shipping method', function () {
    $shippingMethod = app(PrepareOrderAction::class)->prepareShippingMethod($this->placeOrderData);

    assertInstanceOf(ShippingMethod::class, $shippingMethod);
});

it('can prepare payment method', function () {
    $paymentMethod = app(PrepareOrderAction::class)->preparePaymentMethod($this->placeOrderData);

    assertInstanceOf(PaymentMethod::class, $paymentMethod);
});

it('can prepare order', function () {
    $preparedOrder = app(PrepareOrderAction::class)
        ->execute($this->placeOrderData);

    assertInstanceOf(PreparedOrderData::class, $preparedOrder);
});
