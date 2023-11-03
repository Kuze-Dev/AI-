<?php

declare(strict_types=1);

use App\Features\Service\ServiceBase;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\ListServiceOrder;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext()->features()->activate(ServiceBase::class);
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render service order page', function () {
    livewire(ListServiceOrder::class)
        ->assertOk();
});

it('can list service orders', function () {
    $serviceOrders = ServiceOrderFactory::new()
        ->count(2)
        ->create();

    livewire(ListServiceOrder::class)
        ->assertCanSeeTableRecords($serviceOrders)
        ->assertOk();
});
