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
use Domain\Support\MetaData\Models\MetaData;
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
    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->has(
            TaxonomyFactory::new()
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'Main'])
                        ->addSchemaField(['title' => 'Description', 'type' => FieldType::TEXT])
                )
        )
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->has(
            TaxonomyTermFactory::new(['data' => ['description' => 'test']])
                ->for($collection->taxonomies->first())
                ->count(2)
        )
        ->createOne([
            'title' => 'Foo',
            'published_at' => Carbon::now(),
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    livewire(EditCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey(), 'record' => $collectionEntry->getRouteKey()])
        ->assertFormSet([
            'title' => $collectionEntry->title,
            'published_at' => (string) $collectionEntry->published_at->timezone(Auth::user()->timezone),
            'data' => $collectionEntry->data,
            'taxonomies' => [
                $collection->taxonomies->first()->id => $collectionEntry->taxonomyTerms->pluck('id')->toArray(),
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
        ->has(
            TaxonomyFactory::new()
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'Main'])
                        ->addSchemaField(['title' => 'Description', 'type' => FieldType::TEXT])
                )
        )
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->has(
            TaxonomyTermFactory::new(['data' => ['description' => 'test']])
                ->for($collection->taxonomies->first())
                ->count(2)
        )
        ->createOne([
            'title' => 'Foo',
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    $metaDataData = [
        'title' => $collectionEntry->slug,
        'description' => 'Foo description',
        'author' => 'Foo author',
        'keywords' => 'Foo keywords',
    ];

    $collectionEntry->metaData()->create($metaDataData);

    $dateTime = Carbon::now();

    $updatedCollectionEntry = livewire(EditCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey(), 'record' => $collectionEntry->getRouteKey()])
        ->fillForm([
            'title' => 'New Foo',
            'published_at' => $dateTime,
            'data' => ['main' => ['header' => 'Foo updated']],
            'taxonomies' => [
                $collection->taxonomies->first()->id => $collectionEntry->taxonomyTerms->pluck('id'),
            ],
            'meta_data' => [
                'title' => '',
                'description' => '',
                'author' => '',
                'keywords' => '',
            ],
        ])
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors()
        ->instance()
        ->record;

    assertDatabaseHas(CollectionEntry::class, [
        'title' => 'New Foo',
        'published_at' => $dateTime,
        'data' => json_encode(['main' => ['header' => 'Foo updated']]),
    ]);

    assertDatabaseHas(
        MetaData::class,
        [
            'title' => 'New Foo',
            'description' => null,
            'author' => null,
            'keywords' => null,
            'taggable_type' => $updatedCollectionEntry->getMorphClass(),
            'taggable_id' => $updatedCollectionEntry->id,
        ]
    );

    foreach ($collectionEntry->taxonomyTerms as $taxonomyTerm) {
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

    $metaDataData = [
        'title' => $collectionEntry->slug,
        'description' => 'Foo description',
        'author' => 'Foo author',
        'keywords' => 'Foo keywords',
    ];

    $collectionEntry->metaData()->create($metaDataData);

    livewire(EditCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey(), 'record' => $collectionEntry->getRouteKey()])
        ->fillForm(['slug' => 'new-foo'])
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors()
        ->instance()
        ->record;

    assertDatabaseHas(CollectionEntry::class, [
        'id' => $collectionEntry->id,
        'slug' => 'new-foo',
    ]);
    assertDatabaseCount(SlugHistory::class, 3); // 1 (for collection) + 2 (for collection entry)
    assertDatabaseHas(SlugHistory::class, [
        'model_type' => $collectionEntry->getMorphClass(),
        'model_id' => $collectionEntry->id,
        'slug' => 'new-foo',
    ]);
});

it('can edit collection entry to have no taxonomy terms attached', function () {
    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->has(
            TaxonomyFactory::new()
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'Main'])
                        ->addSchemaField(['title' => 'Description', 'type' => FieldType::TEXT])
                )
        )
        ->createOne();

    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->has(
            TaxonomyTermFactory::new(['data' => ['description' => 'test']])
                ->for($collection->taxonomies->first())
                ->count(2)
        )
        ->createOne([
            'title' => 'Foo',
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    $metaDataData = [
        'title' => $collectionEntry->slug,
        'description' => 'Foo description',
        'author' => 'Foo author',
        'keywords' => 'Foo keywords',
    ];

    $collectionEntry->metaData()->create($metaDataData);

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

it('can edit collection entry meta data', function () {
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

    $metaDataData = [
        'title' => $collectionEntry->slug,
        'description' => 'Foo description',
        'author' => 'Foo author',
        'keywords' => 'Foo keywords',
    ];

    $collectionEntry->metaData()->create($metaDataData);

    $dateTime = Carbon::now();

    $updatedMetaData = [
        'title' => 'Updated foo title',
        'description' => 'Updated foo description',
        'author' => 'Updated foo author',
        'keywords' => 'Updated foo keywords',
    ];

    $updatedCollectionEntry = livewire(EditCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey(), 'record' => $collectionEntry->getRouteKey()])
        ->fillForm([
            'title' => 'Updated Foo',
            'slug' => 'updated-foo',
            'published_at' => $dateTime,
            'data' => ['main' => ['header' => 'Foo updated']],
            'taxonomies' => [
                $collection->taxonomies->first()->id => $taxonomyTerms->pluck('id'),
            ],
            'meta_data' => $updatedMetaData,
        ])
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors()
        ->instance()
        ->record;

    assertDatabaseHas(CollectionEntry::class, [
        'title' => 'Updated Foo',
        'published_at' => $dateTime,
        'data' => json_encode(['main' => ['header' => 'Foo updated']]),
    ]);

    assertDatabaseHas(
        MetaData::class,
        array_merge(
            $updatedMetaData,
            [
                'taggable_type' => $updatedCollectionEntry->getMorphClass(),
                'taggable_id' => $updatedCollectionEntry->id,
            ]
        )
    );

    foreach ($taxonomyTerms as $taxonomyTerm) {
        assertDatabaseHas('collection_entry_taxonomy_term', [
            'taxonomy_term_id' => $taxonomyTerm->getKey(),
            'collection_entry_id' => $collectionEntry->getKey(),
        ]);
    }
});

it('can edit collection entry to have no meta data filled', function () {
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

    $metaDataData = [
        'title' => $collectionEntry->slug,
        'description' => 'Foo description',
        'author' => 'Foo author',
        'keywords' => 'Foo keywords',
    ];

    $collectionEntry->metaData()->create($metaDataData);

    $dateTime = Carbon::now();

    $updatedCollectionEntry = livewire(EditCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey(), 'record' => $collectionEntry->getRouteKey()])
        ->fillForm([
            'title' => 'New Foo',
            'slug' => 'new-foo',
            'published_at' => $dateTime,
            'data' => ['main' => ['header' => 'Foo updated']],
            'taxonomies' => [
                $collection->taxonomies->first()->id => $taxonomyTerms->pluck('id'),
            ],
            'meta_data' => [
                'title' => '',
                'description' => '',
                'author' => '',
                'keywords' => '',
            ],
        ])
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors()
        ->instance()
        ->record;

    assertDatabaseHas(CollectionEntry::class, [
        'title' => 'New Foo',
        'published_at' => $dateTime,
        'data' => json_encode(['main' => ['header' => 'Foo updated']]),
    ]);

    assertDatabaseHas(
        MetaData::class,
        [
            'title' => 'New Foo',
            'description' => null,
            'author' => null,
            'keywords' => null,
            'taggable_type' => $updatedCollectionEntry->getMorphClass(),
            'taggable_id' => $updatedCollectionEntry->id,
        ]
    );

    foreach ($taxonomyTerms as $taxonomyTerm) {
        assertDatabaseHas('collection_entry_taxonomy_term', [
            'taxonomy_term_id' => $taxonomyTerm->getKey(),
            'collection_entry_id' => $collectionEntry->getKey(),
        ]);
    }
});
