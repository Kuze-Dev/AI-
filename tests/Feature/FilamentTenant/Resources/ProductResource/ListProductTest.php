<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\ProductResource\Pages\ListProducts;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render product', function () {
    livewire(ListProducts::class)
        ->assertOk();
});
