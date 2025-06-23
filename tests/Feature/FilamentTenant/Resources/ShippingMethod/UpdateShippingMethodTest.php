<?php

declare(strict_types=1);

use App\Features\ECommerce\ECommerceBase;
use App\Features\Shopconfiguration\Shipping\ShippingStorePickup;
use App\FilamentTenant\Resources\ShippingmethodResource\Pages\EditShippingsMethod;
use Domain\Address\Database\Factories\StateFactory;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Domain\ShippingMethod\Models\ShippingMethod;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext(features: [
        ECommerceBase::class,
        ShippingStorePickup::class,
    ]);
    loginAsSuperAdmin();
});

it('can render edit shipping method', function () {

    $record = ShippingMethodFactory::new()->createOne();

    livewire(EditShippingsMethod::class, ['record' => $record->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful();
});

it('can edit shipping method', function () {

    $state = StateFactory::new()->createOne();

    $record = ShippingMethodFactory::new()->createOne([
        'shipper_country_id' => $state->id,
        'shipper_state_id' => $state->id,
    ]);

    livewire(EditShippingsMethod::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'title' => 'Store Pickup',
            'subtitle' => 'InStore Pickup',
            'description' => 'test',
            'driver' => 'store-pickup',
            'ship_from_address' => [
                'address' => '185 BERRY ST',
                'state' => 'CA',
                'city' => 'SAN FRANCISCO',
                'zip5' => '94107',
                'zip4' => '1741',
            ],
        ])->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(ShippingMethod::class, [
        'title' => 'Store Pickup',
        'driver' => 'store-pickup',

    ]);
});

it('can edit update shipping method status', function () {

    StateFactory::new()->createOne();

    $record = ShippingMethodFactory::new()->createOne();

    livewire(EditShippingsMethod::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'title' => 'Store Pickup',
            'subtitle' => 'InStore Pickup',
            'description' => 'test',
            'driver' => 'store-pickup',
            'active' => true,
            'shipper_country_id' => '1',
            'shipper_state_id' => '1',
            'shipper_address' => '123 Test',
            'shipper_city' => 'Test City',
            'shipper_zipcode' => '62423',
        ])->call('save')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(ShippingMethod::class, [
        'title' => 'Store Pickup',
        'driver' => 'store-pickup',
        'active' => true,

    ]);
});
