<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionEntryResource\Pages\ListCollectionEntry;
use Domain\Collection\Database\Factories\CollectionEntryFactory;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Collection\Models\CollectionEntry;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Taxonomy\Models\Taxonomy;
use Filament\Facades\Filament;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    $collection = CollectionFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    livewire(ListCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey()])
        ->assertOk();
});

it('can list collection entries', function () {
    $collection = CollectionFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $collectionEntries = CollectionEntryFactory::new()
        ->for($collection)
        ->count(5)
        ->create();

    livewire(ListCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey()])
        ->assertCanSeeTableRecords($collectionEntries)
        ->assertOk();
});

it('can delete collection entry', function () {
    $collection = CollectionFactory::new()
        ->withDummyBlueprint()
        ->has(TaxonomyFactory::new())
        ->createOne();
    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->has(
            TaxonomyTermFactory::new()
                ->for(Taxonomy::first())
        )
        ->createOne();

    $taxonomyTerm = $collectionEntry->taxonomyTerms->first();

    livewire(ListCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey()])
        ->callTableAction(DeleteAction::class, $collectionEntry)
        ->assertOk();

    assertDatabaseCount(CollectionEntry::class, 0);
    assertDatabaseMissing('collection_entry_taxonomy_term', [
        'collection_entry_id' => $collectionEntry->id,
        'taxonomy_term_id' => $taxonomyTerm->id,
    ]);
});
