<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionEntryResource\Pages\EditCollectionEntry;
use Carbon\Carbon;
use Domain\Collection\Models\CollectionEntry;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Collection\Database\Factories\CollectionEntryFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Support\SlugHistory\SlugHistory;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render collection entry', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $taxonomyTerms = TaxonomyTermFactory::new()
        ->for($taxonomy)
        ->count(2)
        ->create();

    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    $collection->taxonomies()->attach([$taxonomy->getKey()]);

    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->createOne([
            'title' => 'Foo',
            'published_at' => Carbon::now(),
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    $collectionEntry->taxonomyTerms()->attach($taxonomyTerms);

    livewire(EditCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey(), 'record' => $collectionEntry->getRouteKey()])
        ->assertFormSet([
            'title' => $collectionEntry->title,
            'published_at' => (string) $collectionEntry->published_at->timezone(Auth::user()->timezone),
            'data' => $collectionEntry->data,
            'taxonomies' => [
                $taxonomy->getKey() => $taxonomyTerms->pluck('id')->toArray(),
            ],
        ]);
});

it('can edit collection entry', function () {
    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->has(TaxonomyFactory::new())
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    $taxonomyTerms = TaxonomyTermFactory::new()
        ->for($collection->taxonomies->first())
        ->count(2)
        ->create();

    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->createOne([
            'title' => 'Foo',
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    $dateTime = Carbon::now();

    livewire(EditCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey(), 'record' => $collectionEntry->getRouteKey()])
        ->fillForm([
            'title' => 'New Foo',
            'published_at' => $dateTime,
            'data' => ['main' => ['header' => 'Foo updated']],
            'taxonomies' => [
                $collection->taxonomies->first()->id => $taxonomyTerms->pluck('id'),
            ],
        ])
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors();

    assertDatabaseHas(CollectionEntry::class, [
        'title' => 'New Foo',
        'published_at' => $dateTime,
        'data' => json_encode(['main' => ['header' => 'Foo updated']]),
    ]);
    foreach ($taxonomyTerms as $taxonomyTerm) {
        assertDatabaseHas('collection_entry_taxonomy_term', [
            'taxonomy_term_id' => $taxonomyTerm->getKey(),
            'collection_entry_id' => $collectionEntry->getKey(),
        ]);
    }
});

it('can edit collection entry slug', function () {
    $collection = CollectionFactory::new(['name' => 'Test Collection'])
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne();

    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->createOne([
            'title' => 'Foo',
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    livewire(EditCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey(), 'record' => $collectionEntry->getRouteKey()])
        ->fillForm(['slug' => 'new-foo'])
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors();

    assertDatabaseHas(CollectionEntry::class, [
        'id' => $collectionEntry->id,
        'slug' => 'new-foo',
    ]);
    assertDatabaseCount(SlugHistory::class, 3); // 1 (for collection) + 2 (for collection entry)
    assertDatabaseHas(SlugHistory::class, [
        'sluggable_type' => $collectionEntry->getMorphClass(),
        'sluggable_id' => $collectionEntry->id,
        'slug' => 'new-foo',
    ]);
});

it('can edit collection entry to have no taxonomy terms attached', function () {
    $collection = CollectionFactory::new(['name' => 'Test Collection'])
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->has(TaxonomyFactory::new())
        ->createOne();

    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->has(
            TaxonomyTermFactory::new()
                ->for($collection->taxonomies->first())
                ->count(2)
        )
        ->createOne([
            'title' => 'Test',
            'data' => json_encode(['main' => ['header' => 'Foo']]),
        ]);

    livewire(EditCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey(), 'record' => $collectionEntry->getRouteKey()])
        ->fillForm([
            'taxonomies' => [
                $collection->taxonomies->first()->id => [],
            ],
        ])
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors();

    assertDatabaseMissing('collection_entry_taxonomy_term', ['collection_entry_id' => $collectionEntry->getKey()]);
});
