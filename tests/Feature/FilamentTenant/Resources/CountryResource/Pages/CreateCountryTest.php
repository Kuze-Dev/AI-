<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CountryResource\Pages\CreateCountry;
use Domain\Country\Models\Country;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(CreateCountry::class)
        ->assertFormExists()
        ->assertSuccessful();
});

it('can create country', function () {
    livewire(CreateCountry::class)
        ->fillForm([
            'code' => 'USA',
            'name' => 'United States',
            'capital' => 'Washington, D.C.',
            'timezone' => 'America/New_York',
            'language' => 'English',
            'active' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Country::class, ['code' => 'USA', 'name' => 'United States']);
});
