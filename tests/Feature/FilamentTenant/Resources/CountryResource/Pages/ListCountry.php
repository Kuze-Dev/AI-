<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CountryResource\Pages\ListCountry;
use Domain\Address\Models\Country;
use Filament\Facades\Filament;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Livewire\livewire;
use function Pest\Laravel\assertSoftDeleted;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListCountry::class)->assertSuccessful();
});

it('can list countries', function () {
    $country = [
        Country::create([
            'code' => 'USD',
            'name' => 'United States',
            'capital' => 'Washington, D.C.',
            'timezone' => 'America/New_York',
            'language' => 'English',
            'active' => true,
        ]),
        Country::create([
            'code' => 'EUR',
            'name' => 'European Union',
            'capital' => 'Brussels',
            'timezone' => 'Europe/Brussels',
            'language' => 'Multilingual',
            'active' => true,
        ]),
        // Add more countries as needed
    ];

    livewire(ListCountry::class)
        ->assertCanSeeTableRecords($country);
});

it('can delete country', function () {
    $country = Country::create([
        'code' => 'USA',
        'name' => 'United States',
        'capital' => 'Washington, D.C.',
        'timezone' => 'America/New_York',
        'language' => 'English',
        'active' => true,
    ]);

    livewire(ListCountry::class)
        ->callTableAction(DeleteAction::class, $country);

    assertSoftDeleted($country);
});
