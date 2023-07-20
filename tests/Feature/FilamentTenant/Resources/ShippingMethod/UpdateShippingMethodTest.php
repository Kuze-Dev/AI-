<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\ShippingmethodResource\Pages\EditShippingmethod;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Domain\ShippingMethod\Models\ShippingMethod;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render edit shipping method', function () {

    $record = ShippingMethodFactory::new()->createOne();

    livewire(EditShippingmethod::class, ['record' => $record->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful();
});

it('can edit shipping method', function () {

    $record = ShippingMethodFactory::new()->createOne();

    livewire(EditShippingmethod::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'title' => 'Store Pickup',
            'subtitle' => 'InStore Pickup',
            'description' => 'test',
            'driver' => 'store-pickup',
            'ship_from_address' => [
                'address' => '185 BERRY ST',
                'state' => 'CA',
                'city' => 'SAN FRANCISCO',
                'zip3' => '94107',
                'zip4' => '1741',
            ],
        ])->call('save')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(ShippingMethod::class, [
        'title' => 'Store Pickup',
        'driver' => 'store-pickup',

    ]);
});

it('can edit update shipping method status', function () {

    $record = ShippingMethodFactory::new()->createOne();

    livewire(EditShippingmethod::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'title' => 'Store Pickup',
            'subtitle' => 'InStore Pickup',
            'description' => 'test',
            'driver' => 'store-pickup',
            'status' => true,
            'ship_from_address' => [
                'address' => '185 BERRY ST',
                'state' => 'CA',
                'city' => 'SAN FRANCISCO',
                'zip3' => '94107',
                'zip4' => '1741',
            ],
        ])->call('save')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(ShippingMethod::class, [
        'title' => 'Store Pickup',
        'driver' => 'store-pickup',
        'status' => true,

    ]);
});
