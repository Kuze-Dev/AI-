<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CurrencyResource\Pages\EditCurrency;
use Domain\Currency\Models\Currency;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    $currency = Currency::create([
        'code' => 'USD',
        'name' => 'US Dollar',
        'enabled' => true,
        'exchange_rate' => 1.0,
        'default' => false,
    ]);

    livewire(EditCurrency::class, ['record' => $currency->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'code' => $currency->code,
            'name' => $currency->name,
            'enabled' => $currency->enabled,
            'exchange_rate' => $currency->exchange_rate,
            'default' => $currency->default,
        ]);
});

it('can edit currency', function () {
    $currency = Currency::create([
        'code' => 'USD',
        'name' => 'US Dollar',
        'enabled' => true,
        'exchange_rate' => 1.0,
        'default' => false,
    ]);

    livewire(EditCurrency::class, ['record' => $currency->getRouteKey()])
        ->fillForm([
            'code' => 'EUR',
            'name' => 'Euro',
            'enabled' => true,
            'exchange_rate' => 0.85,
            'default' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Currency::class, ['code' => 'EUR', 'name' => 'Euro']);
});
