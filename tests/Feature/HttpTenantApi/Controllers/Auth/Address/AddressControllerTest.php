<?php

declare(strict_types=1);

use Domain\Address\Database\Factories\StateFactory;
use Domain\Address\Models\Address;
use Domain\Customer\Database\Factories\CustomerFactory;
use Laravel\Sanctum\Sanctum;
use Tests\RequestFactories\AddressRequestFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

uses()->group('customer');

beforeEach(function () {
    testInTenantContext();

    $this->customer = CustomerFactory::new()
        ->createOne();

    Sanctum::actingAs($this->customer);
});

todo('can list');

it('can create 1st address and set all default', function (?bool $default) {
    $newData = AddressRequestFactory::new()
        ->withState(StateFactory::new()->createOne())
        ->defaultShipping($default)
        ->defaultBilling($default)
        ->create();

    Address::truncate();
    assertCount(0, $this->customer->refresh()->addresses);

    postJson('api/addresses', $newData)
        ->assertValid()
        ->assertCreated();

    assertCount(1, $this->customer->refresh()->addresses);

    unset($newData['country_id']);

    assertDatabaseHas(Address::class, [
        'customer_id' => $this->customer->getKey(),
        ...$newData,
        'is_default_shipping' => true,
        'is_default_billing' => true,
    ]);
})
    ->with(
        [
            'default' => true,
            'not default' => false,
            'null' => null,
        ]
    );

it('can create then replace new default', function () {
    $newData = AddressRequestFactory::new()
        ->withState(StateFactory::new()->createOne())
        ->defaultShipping()
        ->defaultBilling()
        ->create();

    /** @var \Domain\Address\Models\Address[] $existingAddresses */
    $existingAddresses = $this->customer->refresh()->addresses;
    assertCount(1, $existingAddresses);
    assertTrue($existingAddresses[0]->is_default_billing);
    assertTrue($existingAddresses[0]->is_default_shipping);

    postJson('api/addresses', $newData)
        ->assertValid()
        ->assertCreated();

    $existingAddresses[0]->refresh();
    assertFalse($existingAddresses[0]->is_default_billing);
    assertFalse($existingAddresses[0]->is_default_shipping);

    unset($newData['country_id']);

    assertDatabaseHas(Address::class, [
        'customer_id' => $this->customer->getKey(),
        ...$newData,
    ]);
});

todo('can edit');

todo('can delete');
