<?php

declare(strict_types=1);

use App\Features\ECommerce\ECommerceBase;
use App\Features\Shopconfiguration\Shipping\ShippingStorePickup;
use App\FilamentTenant\Resources\ShippingmethodResource\Pages\CreateShippingsMethod;
use Domain\Address\Database\Factories\StateFactory;
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

it('can render shipping method', function () {
    livewire(CreateShippingsMethod::class)
        ->assertFormExists()
        ->assertSuccessful();
});

it('can create shipping method', function () {

    StateFactory::new()->createOne();

    livewire(CreateShippingsMethod::class)
        ->fillForm([
            'title' => 'Store Pickup',
            'subtitle' => 'InStore Pickup',
            'description' => 'test',
            'driver' => 'store-pickup',
            'shipper_country_id' => 1,
            'shipper_state_id' => 1,
            'shipper_address' => '123 Test',
            'shipper_city' => 'Test City',
            'shipper_zipcode' => '62423',

        ])->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(ShippingMethod::class, [
        'title' => 'Store Pickup',
        'slug' => 'store-pickup',

    ]);
});
