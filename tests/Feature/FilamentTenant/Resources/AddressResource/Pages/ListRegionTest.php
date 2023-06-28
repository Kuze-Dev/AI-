<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Domain\Address\Database\Factories\RegionFactory;
use App\FilamentTenant\Resources\AddressResource\RegionResource\Pages\ListRegion;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListRegion::class)->assertSuccessful();
});

it('can list regions', function () {
    $regions = RegionFactory::new()
        ->count(5)
        ->create();

    livewire(ListRegion::class)->assertCanSeeTableRecords($regions);
});
