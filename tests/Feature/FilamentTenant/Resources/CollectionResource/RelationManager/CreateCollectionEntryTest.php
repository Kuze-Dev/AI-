<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionResource\Pages\CreateCollectionEntry;
use Carbon\Carbon;
use Domain\Collection\Models\CollectionEntry;
use Domain\Collection\Database\Factories\CollectionFactory;
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

it('can create collection entry', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $taxonomyTerm = TaxonomyTermFactory::new()
        ->for($taxonomy)
        ->createOne();

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

    livewire(CreateCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey()])
        ->assertOk()
        ->fillForm([
            'title' => 'Test',
            'slug' => 'test',
            'taxonomies' => [
                $taxonomy->getKey() => [
                    $taxonomyTerm->getKey(),
                ],
            ],
            'data' => ['main' => ['header' => 'Foo']],
            'published_at' => $dateTime,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(
        CollectionEntry::class,
        [
            'id' => $collection->id,
            'title' => 'Test',
            'slug' => 'test',
            'published_at' => $dateTime,
            'data' => json_encode(['main' => ['header' => 'Foo']]),
        ]
    );

    assertDatabaseHas(
        'collection_entries_taxonomy_terms',
        [
            'taxonomy_terms_id' => $taxonomyTerm->getKey(),
            'collection_entries_id' => CollectionEntry::latest()->first()->getKey(),
        ]
    );
});

it('can create collection entry with no taxonomy terms', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $taxonomyTerm = TaxonomyTermFactory::new()
        ->for($taxonomy)
        ->createOne();

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

    livewire(CreateCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey()])
        ->assertOk()
        ->fillForm([
            'title' => 'Test',
            'slug' => 'test',
            'published_at' => $dateTime,
            'data' => ['main' => ['header' => 'Foo']],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(
        CollectionEntry::class,
        [
            'id' => $collection->id,
            'title' => 'Test',
            'slug' => 'test',
            'published_at' => $dateTime,
            'data' => json_encode(['main' => ['header' => 'Foo']]),
        ]
    );

    assertDatabaseMissing(
        'collection_entries_taxonomy_terms',
        [
            'taxonomy_terms_id' => $taxonomyTerm->getKey(),
            'collection_entries_id' => CollectionEntry::latest()->first()->getKey(),
        ]
    );
});

it('can create collection entry with no publish date', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $taxonomyTerm = TaxonomyTermFactory::new()
        ->for($taxonomy)
        ->createOne();

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

    livewire(CreateCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey()])
        ->assertOk()
        ->fillForm([
            'title' => 'Test',
            'slug' => 'test',
            'data' => ['main' => ['header' => 'Foo']],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(
        CollectionEntry::class,
        [
            'id' => $collection->id,
            'title' => 'Test',
            'slug' => 'test',
            'data' => json_encode(['main' => ['header' => 'Foo']]),
        ]
    );

    assertDatabaseMissing(
        'collection_entries_taxonomy_terms',
        [
            'taxonomy_terms_id' => $taxonomyTerm->getKey(),
            'collection_entries_id' => CollectionEntry::latest()->first()->getKey(),
        ]
    );
});
