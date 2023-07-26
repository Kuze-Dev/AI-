<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\ProductResource\Pages\ListProducts;
use Domain\Product\Database\Factories\ProductFactory;
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

it('can list pages', function () {
    $products = ProductFactory::new()
        ->count(5)
        ->create();

    livewire(ListProducts::class)
        ->assertCanSeeTableRecords($products)
        ->assertOk();
});
