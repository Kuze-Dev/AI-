<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxonomyResource\Pages\EditTaxonomy;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Models\Taxonomy;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    $taxonomy = TaxonomyFactory::new()->createOne();

    livewire(EditTaxonomy::class, ['record' => $taxonomy->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet(['name' => $taxonomy->name])
        ->assertOk();
});

it('can edit page', function () {
    $taxonomy = TaxonomyFactory::new()->createOne(['name' => 'old name']);

    livewire(EditTaxonomy::class, ['record' => $taxonomy->getRouteKey()])
        ->fillForm(['name' => 'new name', ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(
        Taxonomy::class,
        [
            'id' => $taxonomy->id,
            'name' => 'new name',
        ]
    );
});
