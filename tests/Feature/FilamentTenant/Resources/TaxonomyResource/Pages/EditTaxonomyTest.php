<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxonomyResource\Pages\EditTaxonomy;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
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
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Description', 'type' => FieldType::TEXT])
        )
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
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Description', 'type' => FieldType::TEXT])
        )
        ->has(TaxonomyTermFactory::new(), 'taxonomyTerms')
        ->createOne();

    livewire(EditTaxonomy::class, ['record' => $taxonomy->getRouteKey()])
        ->fillForm([
            'name' => 'Test Edit Term',
            'terms' => [
                [
                    'name' => 'Test Edit Home',
                    'slug' => 'test-edit-home',
                    'data' => [
                        'main' => [
                            'description' => 'Gwapa siya',
                        ],
                    ],

                ],
                [
                    'name' => 'Test 2 Edit Home',
                    'slug' => 'test-2-edit-home',
                    'data' => [
                        'main' => [
                            'description' => 'Gwapa siya',
                        ],
                    ],
                    'childs' => [
                        [
                            'name' => 'Test 3 Edit Home',
                            'slug' => 'test-3-edit-home',
                            'data' => [
                                'main' => [
                                    'description' => 'Gwapa siya',
                                ],
                            ],
                        ],
                        [
                            'name' => 'Test 4 Edit Home',
                            'slug' => 'test-4-edit-home',
                            'data' => [
                                'main' => [
                                    'description' => 'Gwapa siya',
                                ],
                            ],
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
