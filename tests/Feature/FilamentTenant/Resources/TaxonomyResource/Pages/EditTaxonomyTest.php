<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxonomyResource\Pages\EditTaxonomy;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
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
    $taxonomy = TaxonomyFactory::new()
        ->has(TaxonomyTermFactory::new(), 'taxonomyTerms')
        ->createOne();

    livewire(EditTaxonomy::class, ['record' => $taxonomy->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet(['name' => $taxonomy->name])
        ->assertOk();
});

it('can edit page', function () {
    $taxonomy = TaxonomyFactory::new()
        ->has(TaxonomyTermFactory::new(), 'taxonomyTerms')
        ->createOne();

    livewire(EditTaxonomy::class, ['record' => $taxonomy->getRouteKey()])
        ->fillForm([
            'name' => 'Test Edit Term',
            'terms' => [
                [
                    'name' => 'Test Edit Home',
                    'slug' => 'test-edit-home',
                    'description' => 'Sample Text',
                ],
                [
                    'name' => 'Test 2 Edit Home',
                    'slug' => 'test-2-edit-home',
                    'description' => 'Sample Text',
                    'children' => [
                        [
                            'name' => 'Test 3 Edit Home',
                            'slug' => 'test-3-edit-home',
                            'description' => 'Sample Text',
                        ],
                        [
                            'name' => 'Test 4 Edit Home',
                            'slug' => 'test-4-edit-home',
                            'description' => 'Sample Text',
                        ],
                    ],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(
        Taxonomy::class,
        [
            'name' => 'Test Edit Term',
        ]
    );
});
