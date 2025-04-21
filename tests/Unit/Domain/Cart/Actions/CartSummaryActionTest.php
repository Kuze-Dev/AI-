<?php

declare(strict_types=1);

use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Cart\Actions\CartSummaryAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\DataTransferObjects\CartSummaryShippingData;
use Domain\Cart\DataTransferObjects\CartSummaryTaxData;
use Domain\Cart\DataTransferObjects\SummaryData;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\Shipment\Drivers\StorePickupDriver;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Laravel\Sanctum\Sanctum;

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

    $shippingMethod = ShippingMethodFactory::new()->createOne();

    app(ShippingManagerInterface::class)->extend($shippingMethod->driver->value, fn () => new StorePickupDriver);

    $shippingMethod->update([
        'shipper_country_id' => $country->id,
        'shipper_state_id' => $state->id,
    ]);

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
    $this->cartLines = $cartLines;
});

it('can get subtotal', function () {
    $subtotal = app(CartSummaryAction::class)->getSubTotal($this->cartLines);

    expect($subtotal)->toBeFloat();
});

it('can get shipping fee', function () {
    $shippingTotal = app(CartSummaryAction::class)->getShippingFee(
        $this->cartLines,
        $this->customer,
        ShippingAddressData::fromAddressModel($this->address),
        $this->shippingMethod,
        null,
    );

    expect($shippingTotal)->toBeFloat();
});

it('can get tax', function () {
    $subtotal = app(CartSummaryAction::class)->getSubTotal($this->cartLines);

    $tax = app(CartSummaryAction::class)->getTax($this->country->id, $this->state->id);

    expect($tax)->toBeArray()
        ->toHaveKeys(['taxZone', 'taxDisplay', 'taxPercentage']);

    $taxTotal = $tax['taxPercentage'] ? round($subtotal * $tax['taxPercentage'] / 100, 2) : 0;

    expect($taxTotal)->toBe(0);
});

it('can get discount', function () {
    $subtotal = app(CartSummaryAction::class)->getSubTotal($this->cartLines);

    $discountTotal = app(CartSummaryAction::class)->getDiscount(null, $subtotal, 10.00);

    expect($discountTotal)->toBe(0.0);
});

it('can get cart summary', function () {
    $summary = app(CartSummaryAction::class)->execute(
        $this->cartLines,
        new CartSummaryTaxData($this->country->id, $this->state->id),
        new CartSummaryShippingData(
            $this->customer,
            $this->address,
            $this->shippingMethod
        ),
        null,
        null
    );

    assertInstanceOf(SummaryData::class, $summary);
});
