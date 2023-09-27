<?php

declare(strict_types=1);

use Domain\Address\Database\Factories\AddressFactory;
use Domain\Address\Models\Address;
use Domain\Customer\Database\Factories\CustomerFactory;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

uses()->group('customer');

beforeEach(function () {
    testInTenantContext();

    $this->customer = CustomerFactory::new()
        ->createOne();

    Sanctum::actingAs($this->customer);
});

it('can set default shipping', function () {

    $address = AddressFactory::new()
        ->createOne([
            'customer_id' => $this->customer->id,
            'is_default_shipping' => false,
            'is_default_billing' => false,
        ]);

    postJson('api/addresses/'.$address->getKey().'/set-shipping');

    assertDatabaseHas(Address::class, [
        'customer_id' => $this->customer->id,
        'is_default_shipping' => true,

    ]);

});

it('can set default billing', function () {
    $address = AddressFactory::new()
        ->createOne([
            'customer_id' => $this->customer->id,
            'is_default_billing' => false,
        ]);

    postJson('api/addresses/'.$address->getKey().'/set-billing');

    assertDatabaseHas(Address::class, [
        'customer_id' => $this->customer->id,
        'is_default_billing' => true,
    ]);

});
