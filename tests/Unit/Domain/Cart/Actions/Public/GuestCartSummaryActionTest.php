<?php

declare(strict_types=1);

use Domain\Address\Models\Country;
use Domain\Cart\Actions\PublicCart\GuestCartSummaryAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Cart\DataTransferObjects\GuestCartSummaryShippingData;
use Domain\Cart\DataTransferObjects\SummaryData;
use Domain\Cart\Models\CartLine;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\DataTransferObjects\ReceiverData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\Shipment\Drivers\StorePickupDriver;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;

use function PHPUnit\Framework\assertInstanceOf;

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

    $shippingMethod = ShippingMethodFactory::new()->createOne();

    app(ShippingManagerInterface::class)->extend($shippingMethod->driver->value, fn () => new StorePickupDriver);

    $shippingMethod->update([
        'shipper_country_id' => $country->id,
        'shipper_state_id' => $state->id,
    ]);

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

    $this->country = $country;
    $this->state = $state;
    $this->shippingMethod = $shippingMethod;
    $this->cartLines = $cartLines;

    $this->customer = new ReceiverData(
        first_name: 'Benedict',
        last_name: 'Regore',
    );

    $this->address = new ShippingAddressData(
        address: '185 Berry Street',
        city: 'San Francisco',
        zipcode: '94107',
        code: $state->code,
        state: $state,
        country: $country,
    );
});

it('can get subtotal', function () {
    $subtotal = app(GuestCartSummaryAction::class)->getSubTotal($this->cartLines);

    expect($subtotal)->toBeFloat();
});

it('can get shipping fee', function () {
    $shippingTotal = app(GuestCartSummaryAction::class)->getShippingFee(
        $this->cartLines,
        $this->customer,
        $this->address,
        $this->shippingMethod,
        null,
    );

    expect($shippingTotal)->toBeFloat();
});

it('can get tax', function () {
    $subtotal = app(GuestCartSummaryAction::class)->getSubTotal($this->cartLines);

    $tax = app(GuestCartSummaryAction::class)->getTax($this->country->id, $this->state->id);

    expect($tax)->toBeArray()
        ->toHaveKeys(['taxZone', 'taxDisplay', 'taxPercentage']);

    $taxTotal = $tax['taxPercentage'] ? round($subtotal * $tax['taxPercentage'] / 100, 2) : 0;

    expect($taxTotal)->toBe(0);
});

it('can get discount', function () {
    $subtotal = app(GuestCartSummaryAction::class)->getSubTotal($this->cartLines);

    $discountTotal = app(GuestCartSummaryAction::class)->getDiscount(null, $subtotal, 10.00);

    expect($discountTotal)->toBe(0.0);
});

it('can get cart summary', function () {
    $summary = app(GuestCartSummaryAction::class)->execute(
        $this->cartLines,
        new CartSummaryTaxData($this->country->id, $this->state->id),
        new GuestCartSummaryShippingData(
            $this->customer,
            $this->address,
            $this->shippingMethod
        ),
        null,
        null
    );

    assertInstanceOf(SummaryData::class, $summary);
});
