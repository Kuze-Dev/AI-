<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionEntryResource\Pages\ListCollectionEntry;
use Carbon\Carbon;
use Domain\Collection\Database\Factories\CollectionEntryFactory;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
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

it('can filter collection entries by published at range', function () {
    $collection = CollectionFactory::new()
        ->publishDateBehaviour()
        ->withDummyBlueprint()
        ->createOne();

    CollectionEntryFactory::new()
        ->for($collection)
        ->count(3)
        ->sequence(
            ['published_at' => Carbon::now()->subWeeks(2)],
            ['published_at' => Carbon::now()],
            ['published_at' => Carbon::now()->addWeeks(2)],
        )
        ->create([]);

    livewire(ListCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey()])
        ->assertCountTableRecords(3)
        ->filterTable('published_at_range', [
            'published_at_from' => Carbon::now()->subDay(),
            'published_at_to' => null,
        ])
        ->assertCountTableRecords(2)
        ->filterTable('published_at_range', [
            'published_at_from' => null,
            'published_at_to' => Carbon::now()->addDay(),
        ])
        ->assertCountTableRecords(2)
        ->filterTable('published_at_range', [
            'published_at_from' => Carbon::now()->subDay(),
            'published_at_to' => Carbon::now()->addDay(),
        ])
        ->assertCountTableRecords(1)
        ->assertOk();
});

it('can filter collection entries by published at year month', function () {
    $collection = CollectionFactory::new()
        ->publishDateBehaviour()
        ->withDummyBlueprint()
        ->createOne();

    CollectionEntryFactory::new()
        ->for($collection)
        ->count(3)
        ->sequence(
            ['published_at' => Carbon::now()->subYear()],
            ['published_at' => Carbon::now()->subMonth()],
            ['published_at' => Carbon::now()],
        )
        ->create([]);

    livewire(ListCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey()])
        ->assertCountTableRecords(3)
        ->filterTable('published_at_year_month', [
            'published_at_year' => Carbon::now()->year,
            'published_at_month' => null,
        ])
        ->assertCountTableRecords(2)
        ->filterTable('published_at_year_month', [
            'published_at_year' => Carbon::now()->year,
            'published_at_month' => Carbon::now()->month,
        ])
        ->assertCountTableRecords(1)
        ->assertOk();
});

it('can filter collection entries by taxonomies', function () {
    $collection = CollectionFactory::new()
        ->publishDateBehaviour()
        ->withDummyBlueprint()
        ->has(
            TaxonomyFactory::new(['name' => 'Category'])
                ->withDummyBlueprint()
        )
        ->createOne();

    CollectionEntryFactory::new()
        ->for($collection)
        ->has(
            TaxonomyTermFactory::new(['name' => 'Laravel'])
                ->for($collection->taxonomies->first())
        )
        ->create();
    CollectionEntryFactory::new()
        ->for($collection)
        ->has(
            TaxonomyTermFactory::new(['name' => 'Livewire'])
                ->for($collection->taxonomies->first())
        )
        ->create();
    CollectionEntryFactory::new()
        ->for($collection)
        ->create();

    livewire(ListCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey()])
        ->assertCountTableRecords(3)
        ->filterTable('taxonomies', ['category' => ['laravel']])
        ->assertCountTableRecords(1)
        ->filterTable('taxonomies', ['category' => ['livewire']])
        ->assertCountTableRecords(1)
        ->assertOk();
});

it('can delete collection entry', function () {
    $collection = CollectionFactory::new()
        ->withDummyBlueprint()
        ->has(
            TaxonomyFactory::new()
                ->withDummyBlueprint()
        )
        ->createOne();
    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->has(
            TaxonomyTermFactory::new()
                ->for($collection->taxonomies->first())
        )
        ->createOne();

    $taxonomyTerm = $collectionEntry->taxonomyTerms->first();
    $metaData = $collectionEntry->metaData;

    livewire(ListCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey()])
        ->callTableAction(DeleteAction::class, $collectionEntry)
        ->assertOk();

    assertModelMissing($collectionEntry);
    assertDatabaseMissing('collection_entry_taxonomy_term', [
        'collection_entry_id' => $collectionEntry->id,
        'taxonomy_term_id' => $taxonomyTerm->id,
    ]);
    assertModelMissing($metaData);
});
