<?php

declare(strict_types=1);

use App\Features\ECommerce\ECommerceBase;
use App\Features\Shopconfiguration\Shipping\ShippingStorePickup;
use App\FilamentTenant\Resources\ShippingmethodResource\Pages\ListShippingmethods;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Filament\Facades\Filament;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();

    tenancy()->tenant->features()->activate(ECommerceBase::class);
    tenancy()->tenant->features()->activate(ShippingStorePickup::class);
});

it('can render Shipping Method', function () {
    livewire(ListShippingmethods::class)
        ->assertSuccessful();
});

it('can list Shipping Method', function () {
    $shippingMethod = ShippingMethodFactory::new()->count(5)->create();

    livewire(ListShippingmethods::class)
        ->assertCanSeeTableRecords($shippingMethod);
});

// it('can delete Shipping Method', function () {
//     $record = ShippingMethodFactory::new()
//         ->createOne();

//     livewire(ListShippingmethods::class)
//         ->callTableAction(DeleteAction::class, $record)
//         ->assertOk();

//     assertModelMissing($record);
// })->only();
