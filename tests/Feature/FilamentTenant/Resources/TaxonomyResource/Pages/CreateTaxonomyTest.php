<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxonomyResource\Pages\CreateTaxonomy;
use Domain\Taxonomy\Models\Taxonomy;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(CreateTaxonomy::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create page', function () {
    livewire(CreateTaxonomy::class)
        ->fillForm([
            'name' => 'Test',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseCount(Taxonomy::class, 1);
});
