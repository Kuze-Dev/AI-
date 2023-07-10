<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\GlobalsResource\Pages\CreateGlobals;
use App\FilamentTenant\Resources\ShippingmethodResource\Pages\CreateShippingmethod;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Globals\Models\Globals;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render shipping method', function () {
    livewire(CreateShippingmethod::class)
        ->assertFormExists()
        ->assertSuccessful();
})->only();

it('can create shipping method', function () {
  

    livewire(CreateShippingmethod::class)
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
            ]
        ])->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Globals::class, [
        'name' => 'Store Pickup',
        'slug' => 'store-pickup',
       
    ]);
})->only();
