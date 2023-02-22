<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionResource\Pages\ListCollection;
use Domain\Collection\Database\Factories\CollectionEntryFactory;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Filament\Facades\Filament;
use Filament\Pages\Actions\DeleteAction;

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
    $collections = CollectionFactory::new()
        ->withDummyBlueprint()
        ->count(5)
        ->create();

    livewire(ListCollection::class)
        ->assertCanSeeTableRecords($collections)
        ->assertOk();
});

it('can delete collection', function () {
    $collection = CollectionFactory::new()
        ->has(
            TaxonomyFactory::new()
                ->withDummyBlueprint()
        )
        ->withDummyBlueprint()
        ->createOne();
    $taxonomy = $collection->taxonomies->first();

    livewire(ListCollection::class)
        ->callTableAction(DeleteAction::class, $collection)
        ->assertOk();

    assertModelMissing($collection);
    assertDatabaseMissing('collection_taxonomy', [
        'collection_id' => $collection->id,
        'taxonomy_id' => $taxonomy->id,
    ]);
});

it('can not delete collection with existing entries', function () {
    $collection = CollectionFactory::new()
        ->has(CollectionEntryFactory::new())
        ->withDummyBlueprint()
        ->createOne();

    livewire(ListCollection::class)
        ->callTableAction(DeleteAction::class, $collection)
        ->assertOk();
})->throws(DeleteRestrictedException::class);
