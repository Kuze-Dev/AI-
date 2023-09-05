<?php

declare(strict_types=1);

use App\Features\ECommerce\ECommerceBase;
use App\Features\ECommerce\ShippingStorePickup;
use App\FilamentTenant\Resources\ShippingmethodResource\Pages\CreateShippingmethod;
use Domain\Address\Database\Factories\StateFactory;
use Domain\ShippingMethod\Models\ShippingMethod;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();

    tenancy()->tenant->features()->activate(ECommerceBase::class);
    tenancy()->tenant->features()->activate(ShippingStorePickup::class);
});

it('can render shipping method', function () {
    livewire(CreateShippingmethod::class)
        ->assertFormExists()
        ->assertSuccessful();
});

it('can create shipping method', function () {

    StateFactory::new()->createOne();

    livewire(CreateShippingmethod::class)
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
