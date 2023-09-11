<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\OrderResource\Pages\ListOrders;
use Domain\Order\Database\Factories\OrderFactory;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render order page', function () {
    livewire(ListOrders::class)
        ->assertOk();
});

it('can list orders', function () {
    $orders = OrderFactory::new()
        ->count(5)
        ->create();

    livewire(ListOrders::class)
        ->assertCanSeeTableRecords($orders)
        ->assertOk();
});
