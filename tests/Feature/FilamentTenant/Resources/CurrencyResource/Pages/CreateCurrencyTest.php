<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CurrencyResource\Pages\CreateCurrency;
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
    livewire(CreateCurrency::class)
        ->assertFormExists()
        ->assertSuccessful();
});

it('can create currency', function () {
    livewire(CreateCurrency::class)
        ->fillForm([
            'code' => 'USD',
            'name' => 'US Dollar',
            'enabled' => true,
            'exchange_rate' => 1.0,
            'default' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Currency::class, ['code' => 'USD', 'name' => 'US Dollar']);
});
