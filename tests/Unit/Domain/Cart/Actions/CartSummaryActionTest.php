<?php

declare(strict_types=1);

use Domain\Address\Database\Factories\StateFactory;
use Domain\Address\Enums\AddressLabelAs;
use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Cart\Actions\CartSummaryAction;
use Domain\Cart\Database\Factories\CartFactory;
use Domain\Cart\Database\Factories\CartLineFactory;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Product\Database\Factories\ProductFactory;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Laravel\Sanctum\Sanctum;
use Tests\RequestFactories\AddressRequestFactory;

beforeEach(function () {
    testInTenantContext();

    $country = Country::create([
        'code' => "US",
        "name" => "United States",
        "capital" => "Washington",
        "timezone" => "UTC-10:00",
        "active" => true
    ]);

    $state = $country->states()->create([
        'name' => "CA",
        'code' => "BH",
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

    Sanctum::actingAs($customer);

    ProductFactory::new()->times(3)->create();

    CartFactory::new()->setCustomerId($customer->id)->createOne();

    $cartLines = CartLineFactory::new()->times(3)
        ->afterCreating(function (CartLine $cartLine, $index) {
            $cartLine->purchasable_id = $index + 1;
            $cartLine->save();
        })->create();

    $this->cartLines = $cartLines;
    $this->customer = $customer;

    $this->country = $country;
    $this->state = $state;
    $this->address = $address;
});

// it('can get subtotal', function () {
//     $subtotal = app(CartSummaryAction::class)->getSubTotal($this->cartLines);

//     expect($subtotal)->toBeFloat();
// });

// it('can get shipping fee', function () {
//     $shippingMethod = ShippingMethodFactory::new()->createOne();

//     $shippingMethod->update([
//         'shipper_country_id' => $this->country->id,
//         'shipper_state_id' => $this->state->id,
//     ]);

//     $shippingTotal = app(CartSummaryAction::class)->getShippingFee(
//         $this->cartLines,
//         $this->customer,
//         $this->address,
//         $shippingMethod,
//         null,
//     );

//     expect($shippingTotal)->toBeFloat();
// });

// it('can get tax', function () {
// });

// it('can get discount', function () {
// });

// it('can get cart summary', function () {
// });
