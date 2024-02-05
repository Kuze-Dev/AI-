<?php

declare(strict_types=1);

use App\Features\ECommerce\ECommerceBase;
use App\Features\Shopconfiguration\Shipping\ShippingStorePickup;
use App\FilamentTenant\Resources\ShippingmethodResource\Pages\ListShippingMethods;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext(features: [
        ECommerceBase::class,
        ShippingStorePickup::class,
    ]);
    loginAsSuperAdmin();
});

it('can render Shipping Method', function () {
    livewire(ListShippingMethods::class)
        ->assertSuccessful();
});

it('can list Shipping Method', function () {
    $shippingMethod = ShippingMethodFactory::new()->count(5)->create();

    livewire(ListShippingMethods::class)
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
