<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CountryResource\Pages\ListCountry;
use Domain\Address\Database\Factories\CountryFactory;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
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
