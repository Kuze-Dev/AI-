<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use App\FilamentTenant\Resources\AddressResource\StateResource\Pages\ListState;
use Domain\Address\Database\Factories\StateFactory;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListState::class)->assertSuccessful();
});

it('can list states', function () {
    $states = StateFactory::new()
        ->count(5)
        ->create();

    livewire(ListState::class)->assertCanSeeTableRecords($states);
});
