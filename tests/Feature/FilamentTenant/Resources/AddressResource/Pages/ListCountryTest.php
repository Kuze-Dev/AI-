<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\AddressResource\CountryResource\Pages\ListCountry;
use Filament\Facades\Filament;
use Domain\Address\Database\Factories\CountryFactory;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListCountry::class)->assertSuccessful();
});

it('can list countries', function () {
    $countries = CountryFactory::new()
        ->count(5)
        ->create();

    livewire(ListCountry::class)->assertCanSeeTableRecords($countries);
});
