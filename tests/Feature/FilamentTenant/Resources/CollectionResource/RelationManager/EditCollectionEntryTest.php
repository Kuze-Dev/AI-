<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionResource\Pages\EditCollectionEntry;
use Carbon\Carbon;
use Domain\Collection\Models\CollectionEntry;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Collection\Database\Factories\CollectionEntryFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can edit collection entry', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $taxonomyTermsInitial = TaxonomyTermFactory::new()
        ->for($taxonomy)
        ->count(2)
        ->create();

    $taxonomyTermsForUpdate = TaxonomyTermFactory::new()
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

    $dateTime = Carbon::now();

    $originalData = [
        'title' => 'Test',
        'slug' => 'test',
        'published_at' => $dateTime,
        'data' => json_encode(['main' => ['header' => 'Foo']]),
    ];

    $collectionEntry = CollectionEntryFactory::new()
        ->for(
            $collection
        )
        ->createOne($originalData);

    $collectionEntry->taxonomyTerms()->attach($taxonomyTermsInitial->pluck('id'));

    $newData = [
        'title' => 'Test update',
        'data' => ['main' => ['header' => 'Foo updated']],
        'taxonomies' => [
            $taxonomy->getKey() => $taxonomyTermsForUpdate->pluck('id'),
        ],
    ];

    livewire(EditCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey(), 'record' => $collectionEntry->getRouteKey()])
        ->fillForm(
            $newData
        )
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors();

    $latestCollectionEntry = CollectionEntry::latest()->first();

    // Check if terms has been removed from collection entry
    foreach ($taxonomyTermsInitial as $initialTerms) {
        assertDatabaseMissing(
            'collection_entry_taxonomy_term',
            [
                'taxonomy_term_id' => $initialTerms->getKey(),
                'collection_entry_id' => $latestCollectionEntry->getKey(),
            ]
        );
    }

    // Check if the newly assigned taxonomy terms
    // has been saved to the database
    foreach ($taxonomyTermsForUpdate as $updatedTerms) {
        assertDatabaseHas(
            'collection_entry_taxonomy_term',
            [
                'taxonomy_term_id' => $updatedTerms->getKey(),
                'collection_entry_id' => $latestCollectionEntry->getKey(),
            ]
        );
    }

    assertDatabaseHas(
        CollectionEntry::class,
        [
            'title' => 'Test update',
            'data' => json_encode(['main' => ['header' => 'Foo updated']]),
        ]
    );
});

it('can edit collection entry to have no taxonomy terms attached', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $taxonomyTermsInitial = TaxonomyTermFactory::new()
        ->for($taxonomy)
        ->count(2)
        ->create();

    $taxonomyTermsForUpdate = TaxonomyTermFactory::new()
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

    $dateTime = Carbon::now();

    $originalData = [
        'title' => 'Test',
        'slug' => 'test',
        'published_at' => $dateTime,
        'data' => json_encode(['main' => ['header' => 'Foo']]),
    ];

    $collectionEntry = CollectionEntryFactory::new()
        ->for(
            $collection
        )
        ->createOne($originalData);

    $collectionEntry->taxonomyTerms()->attach($taxonomyTermsInitial->pluck('id'));

    $newData = [
        'title' => 'Test update',
        'data' => ['main' => ['header' => 'Foo updated']],
        'taxonomies' => [
            $taxonomy->getKey() => [],
        ],
    ];

    livewire(EditCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey(), 'record' => $collectionEntry->getRouteKey()])
        ->fillForm(
            $newData
        )
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors();

    $latestCollectionEntry = CollectionEntry::latest()->first();

    // Check if terms has been removed from collection entry
    foreach ($taxonomyTermsInitial as $initialTerms) {
        assertDatabaseMissing(
            'collection_entry_taxonomy_term',
            [
                'taxonomy_term_id' => $initialTerms->getKey(),
                'collection_entry_id' => $latestCollectionEntry->getKey(),
            ]
        );
    }

    assertDatabaseHas(
        CollectionEntry::class,
        [
            'title' => 'Test update',
            'data' => json_encode(['main' => ['header' => 'Foo updated']]),
        ]
    );
});
