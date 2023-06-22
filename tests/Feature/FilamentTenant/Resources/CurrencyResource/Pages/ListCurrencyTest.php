<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CurrencyResource\Pages\ListCurrency;
use Domain\Currency\Models\Currency;
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
    livewire(ListCurrency::class)->assertSuccessful();
});

it('can list currencies', function () {
    $currency = [
        Currency::create([
            'code' => 'USD',
            'name' => 'US Dollar',
            'enabled' => true,
            'exchange_rate' => 1.0,
            'default' => false,
        ]),
        Currency::create([
            'code' => 'EUR',
            'name' => 'Euro',
            'enabled' => true,
            'exchange_rate' => 0.8,
            'default' => false,
        ]),
        // Add more currencies as needed
    ];

    livewire(ListCurrency::class)
        ->assertCanSeeTableRecords($currency);
});

it('can delete currency', function () {
    $currency = Currency::create([
        'code' => 'USD',
        'name' => 'US Dollar',
        'enabled' => true,
        'exchange_rate' => 1.0,
        'default' => false,
    ]);

    livewire(ListCurrency::class)
        ->callTableAction(DeleteAction::class, $currency);

    assertSoftDeleted($currency);
});
