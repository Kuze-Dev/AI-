<?php

declare(strict_types=1);

use App\Settings\OrderSettings;
use Domain\Address\Database\Factories\CountryFactory;
use Domain\Cart\Actions\CheckoutAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\DataTransferObjects\CheckoutData;
use Domain\Cart\Models\CartLine;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Currency\Models\Currency;
use Domain\Order\Actions\PublicOrder\GuestPrepareOrderAction;
use Domain\Order\DataTransferObjects\GuestOrderAddressData;
use Domain\Order\DataTransferObjects\GuestPlaceOrderData;
use Domain\Order\DataTransferObjects\GuestPreparedOrderData;
use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Contracts\PaymentManagerInterface;
use Domain\Payments\Providers\OfflinePayment;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\Drivers\StorePickupDriver;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Domain\ShippingMethod\Models\ShippingMethod;

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

    $shippingMethod = ShippingMethodFactory::new()->createOne(['title' => 'Store Pickup']);

    app(ShippingManagerInterface::class)->extend($shippingMethod->driver->value, fn () => new StorePickupDriver);

    $shippingMethod->update([
        'shipper_country_id' => $country->id,
        'shipper_state_id' => $state->id,
    ]);

    $paymentMethod = PaymentMethodFactory::new()->createOne(['title' => 'Cod']);

    app(PaymentManagerInterface::class)->extend($paymentMethod->slug, fn () => new OfflinePayment);

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

    $cartLineIds = $cartLines->pluck('uuid')->toArray();

    $reference = app(CheckoutAction::class)
        ->execute(CheckoutData::fromArray(['cart_line_ids' => $cartLineIds]));

    $shippingAddress = [
        'country_id' => 'US',
        'state_id' => $state->id,
        'address_line_1' => '185 Berry Street',
        'zip_code' => '94107',
        'city' => 'San Francisco',
        'label_as' => 'office',
    ];

    $validatedData = [
        'customer' => [
            'first_name' => 'Benedict',
            'last_name' => 'Regore',
            'mobile' => '09208024445',
            'email' => 'benedict.halcyondigital@gmail.com',
        ],
        'cart_reference' => $reference,
        'addresses' => [
            'shipping' => $shippingAddress,
            'billing' => $shippingAddress,
        ],
        'payment_method' => $paymentMethod->slug,
        'shipping_method' => $shippingMethod->slug,
    ];

    $placeOrderData = GuestPlaceOrderData::fromArray($validatedData);

    $this->country = $country;
    $this->state = $state;
    $this->shippingMethod = $shippingMethod;
    $this->paymentMethod = $paymentMethod;
    $this->cartLines = $cartLines;
    $this->placeOrderData = $placeOrderData;
});

it('can prepare address', function () {
    $addresses = app(GuestPrepareOrderAction::class)->prepareAddress($this->placeOrderData);

    expect($addresses)->toHaveKey('shippingAddress');
    expect($addresses)->toHaveKey('billingAddress');

    $shippingAddress = $addresses['shippingAddress'];
    $billingAddress = $addresses['billingAddress'];

    assertInstanceOf(GuestOrderAddressData::class, $shippingAddress);
    assertInstanceOf(GuestOrderAddressData::class, $billingAddress);
});

it('can prepare currency', function () {

    $currency = app(GuestPrepareOrderAction::class)->prepareCurrency();

    assertInstanceOf(Currency::class, $currency);
});

it('can prepare cartlines', function () {
    $cartLines = app(GuestPrepareOrderAction::class)->prepareCartLines($this->placeOrderData);

    $cartLines->each(function ($item) {
        expect($item)->toBeInstanceOf(CartLine::class);
    });
});

it('can prepare tax', function () {
    $countries = app(GuestPrepareOrderAction::class)->prepareCountry($this->placeOrderData);
    $states = app(GuestPrepareOrderAction::class)->prepareState($this->placeOrderData);

    $taxZone = app(GuestPrepareOrderAction::class)->prepareTax($countries->billingCountry, $states->billingState);

    expect($taxZone)->toBe(null);
});

it('can prepare discount', function () {
    $discount = app(GuestPrepareOrderAction::class)->prepareDiscount($this->placeOrderData);

    expect($discount)->toBe(null);
});

it('can prepare shipping method', function () {
    $shippingMethod = app(GuestPrepareOrderAction::class)->prepareShippingMethod($this->placeOrderData);

    assertInstanceOf(ShippingMethod::class, $shippingMethod);
});

it('can prepare payment method', function () {
    $paymentMethod = app(GuestPrepareOrderAction::class)->preparePaymentMethod($this->placeOrderData);

    assertInstanceOf(PaymentMethod::class, $paymentMethod);
});

it('can prepare order', function () {
    $preparedOrder = app(GuestPrepareOrderAction::class)
        ->execute($this->placeOrderData);

    assertInstanceOf(GuestPreparedOrderData::class, $preparedOrder);
});
