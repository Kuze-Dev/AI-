<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxonomyResource\Pages\EditTaxonomy;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Internationalization\Database\Factories\LocaleFactory;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Taxonomy\Models\Taxonomy;
<<<<<<< HEAD
=======
use Domain\Taxonomy\Models\TaxonomyTerm;
use Filament\Facades\Filament;
>>>>>>> develop

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
    LocaleFactory::createDefault();
});

it('can render taxonomy page', function () {
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

it('can edit taxonomy', function () {
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
                    'data' => [
                        'main' => [
                            'description' => 'loren ipsum',
                        ],
                    ],

                ],
                [
                    'name' => 'Test 2 Edit Home',
                    'data' => [
                        'main' => [
                            'description' => 'loren ipsum',
                        ],
                    ],
                    'children' => [
                        [
                            'name' => 'Test 3 Edit Home',
                            'data' => [
                                'main' => [
                                    'description' => 'loren ipsum',
                                ],
                            ],
                        ],
                        [
                            'name' => 'Test 4 Edit Home',
                            'data' => [
                                'main' => [
                                    'description' => 'loren ipsum',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Taxonomy::class, ['name' => 'Test Edit Term']);
});

it('can edit taxonomy and add terms with route url', function () {
    $taxonomy = TaxonomyFactory::new([
        'name' => 'Collection',
    ])
        ->for(
            BlueprintFactory::new([])
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Description', 'type' => FieldType::TEXT])
        )
        ->has(TaxonomyTermFactory::new(), 'taxonomyTerms')
        ->createOne();

    livewire(EditTaxonomy::class, ['record' => $taxonomy->getRouteKey()])
        ->fillForm([
            'name' => 'Collection',
            'has_route' => true,
            'terms' => [
                [
                    'name' => 'entryone',
                    'is_custom' => false,
                    'url' => '/collection/entryone',
                    'data' => [
                        'main' => [
                            'description' => 'loren ipsum',
                        ],
                    ],

                ],
                [
                    'name' => 'entrytwo',
                    'is_custom' => false,
                    '/collection/entrytwo',
                    'data' => [
                        'main' => [
                            'description' => 'loren ipsum',
                        ],
                    ],
                    'children' => [
                        [
                            'name' => 'Entrytwo One',
                            'is_custom' => true,
                            'url' => '/collection/entrytwo/entrytwo-one',
                            'data' => [
                                'main' => [
                                    'description' => 'loren ipsum',
                                ],
                            ],
                        ],
                        [
                            'name' => 'Entrytwo Two',
                            'is_custom' => true,
                            'url' => '/collection/entrytwo/entrytwo-two',
                            'data' => [
                                'main' => [
                                    'description' => 'loren ipsum',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Taxonomy::class, ['name' => 'Collection']);

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $taxonomy->getMorphClass(),
        'model_id' => $taxonomy->getKey(),
        'url' => Taxonomy::generateRouteUrl($taxonomy, $taxonomy->toArray()),
        'is_override' => false,
    ]);

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => app(TaxonomyTerm::class)->getMorphClass(),
        'url' => '/collection/entryone',
        'is_override' => false,
    ]);

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => app(TaxonomyTerm::class)->getMorphClass(),
        'url' => '/collection/entrytwo/entrytwo-two',
        'is_override' => true,
    ]);

});
