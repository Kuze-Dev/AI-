<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CurrencyResource\Pages\ListCurrency;
use Filament\Facades\Filament;

use Domain\Currency\Database\Factories\CurrencyFactory;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListCurrency::class)->assertSuccessful();
});

it('can list currencies', function () {
    $currencies = CurrencyFactory::new()
        ->count(5)
        ->create();

    livewire(ListCurrency::class)->assertCanSeeTableRecords($currencies);
});
