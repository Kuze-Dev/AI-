<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionResource\Pages\ListCollection;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Filament\Facades\Filament;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render collection', function () {
    livewire(ListCollection::class)
        ->assertOk();
});

it('can list collections', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $collections = CollectionFactory::new()
        ->withDummyBlueprint()
        ->count(5)
        ->create();

    foreach ($collections as $collection) {
        $collection->taxonomies()->attach([$taxonomy->getKey()]);
    }
    livewire(ListCollection::class)
        ->assertCanSeeTableRecords($collections)
        ->assertOk();

    foreach ($collections as $collection) {
        assertDatabaseHas(
            'collection_taxonomy',
            [
                'taxonomy_id' => $taxonomy->getKey(),
                'collection_id' => $collection->getKey(),
            ]
        );
    }
});

it('can delete collection', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $collection = CollectionFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $collection->taxonomies()->attach([$taxonomy->getKey()]);

    livewire(ListCollection::class)
        ->callTableAction(DeleteAction::class, $collection)
        ->assertOk();

    assertModelMissing($collection);
    assertDatabaseMissing(
        'collection_taxonomy',
        [
            'taxonomy_id' => $taxonomy->getKey(),
            'collection_id' => $collection->getKey(),
        ]
    );
});
