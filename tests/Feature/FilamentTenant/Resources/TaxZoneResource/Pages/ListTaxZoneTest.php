<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxZoneResource\Pages\ListTaxZones;
use Domain\Taxation\Database\Factories\TaxZoneFactory;
use Filament\Facades\Filament;

use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListTaxZones::class)
        ->assertOk();
});

it('can list taxonomys', function () {
    $taxZones = TaxZoneFactory::new()
        ->count(5)
        ->create();

    livewire(ListTaxZones::class)
        ->assertCanSeeTableRecords($taxZones)
        ->assertOk();
});

it('can delete taxonomy', function () {
    $taxZone = TaxZoneFactory::new()
        ->createOne();

    livewire(ListTaxZones::class)
        ->callTableAction(DeleteAction::class, $taxZone)
        ->assertOk();

    assertModelMissing($taxZone);
});
