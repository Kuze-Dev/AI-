<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CountryResource\Pages\EditCountry;
use Domain\Address\Models\Country;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    $country = Country::create([
        'code' => 'USA',
        'name' => 'United States',
        'capital' => 'Washington, D.C.',
        'timezone' => 'America/New_York',
        'language' => 'English',
        'active' => true,
    ]);

    livewire(EditCountry::class, ['record' => $country->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'code' => $country->code,
            'name' => $country->name,
            'capital' => $country->capital,
            'timezone' => $country->timezone,
            'language' => $country->language,
            'active' => $country->active,
        ]);
});

it('can edit country', function () {
    $country = Country::create([
        'code' => 'USA',
        'name' => 'United States',
        'capital' => 'Washington, D.C.',
        'timezone' => 'America/New_York',
        'language' => 'English',
        'active' => true,
    ]);

    livewire(EditCountry::class, ['record' => $country->getRouteKey()])
        ->fillForm([
            'code' => 'EUR',
            'name' => 'European Union',
            'capital' => 'Brussels',
            'timezone' => 'Europe/Brussels',
            'language' => 'Multilingual',
            'active' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Country::class, ['code' => 'EUR', 'name' => 'European Union']);
});
