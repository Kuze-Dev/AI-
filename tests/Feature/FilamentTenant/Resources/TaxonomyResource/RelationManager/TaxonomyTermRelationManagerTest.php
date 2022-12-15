<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxonomyResource\RelationManagers\TaxonomyTermsRelationManager;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render component', function () {
    $taxonomy = TaxonomyFactory::new()->createOne();

    $taxonomyTerms = TaxonomyTermFactory::new()->for($taxonomy)->count(5)->create();

    livewire(TaxonomyTermsRelationManager::class, ['ownerRecord' => $taxonomy])
        ->assertOk()
        ->assertCanSeeTableRecords($taxonomyTerms);
});

it('can create taxonomy term', function () {
    $taxonomy = TaxonomyFactory::new()->createOne();

    livewire(TaxonomyTermsRelationManager::class, ['ownerRecord' => $taxonomy])
        ->assertOk()
        ->callTableAction('create', data: [
            'name' => 'Test',
            'slug' => 'test',
        ])
        ->assertHasNoTableActionErrors();

    assertDatabaseHas(
        TaxonomyTerm::class,
        [
            'id' => $taxonomy->id,
            'name' => 'Test',
            'slug' => 'test',
        ]
    );
});

it('can edit taxonomy term', function () {
    $taxonomy = TaxonomyFactory::new()->createOne();

    $taxonomyTerm = TaxonomyTermFactory::new()->for($taxonomy)->createOne();

    livewire(TaxonomyTermsRelationManager::class, ['ownerRecord' => $taxonomy])
        ->assertOk()
        ->callTableAction('edit', $taxonomyTerm, [
            'name' => 'Test',
            'slug' => 'test',
        ])
        ->assertHasNoTableActionErrors();

    assertDatabaseHas(
        TaxonomyTerm::class,
        [
            'id' => $taxonomy->id,
            'name' => 'Test',
            'slug' => $taxonomyTerm->slug,
        ]
    );
});

it('can delete taxonomy term', function () {
    $taxonomy = TaxonomyFactory::new()->createOne();

    $taxonomyTerm = TaxonomyTermFactory::new()->for($taxonomy)->createOne();

    livewire(TaxonomyTermsRelationManager::class, ['ownerRecord' => $taxonomy])
        ->assertOk()
        ->callTableAction('delete', $taxonomyTerm);

    assertModelMissing($taxonomyTerm);
});
