<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionEntryResource\Pages\CreateCollectionEntry;
use Carbon\Carbon;
use Domain\Collection\Models\CollectionEntry;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Support\SlugHistory\SlugHistory;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can create collection entry', function () {
    $collection = CollectionFactory::new(['name' => 'Test Collection'])
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne();

    $collectionEntry = livewire(CreateCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey()])
        ->assertOk()
        ->fillForm([
            'title' => 'Test',
            'data' => ['main' => ['header' => 'Foo']],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->instance()
        ->record;

    assertDatabaseHas(
        CollectionEntry::class,
        [
            'id' => $collection->id,
            'title' => 'Test',
            'slug' => 'test',
            'data' => json_encode(['main' => ['header' => 'Foo']]),
        ]
    );
    assertDatabaseHas(SlugHistory::class, [
        'model_type' => $collectionEntry->getMorphClass(),
        'model_id' => $collectionEntry->id,
    ]);
});

it('can create collection entry with taxonomy terms', function () {
    $collection = CollectionFactory::new(['name' => 'Test Collection'])
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
                ->has(TaxonomyTermFactory::new(['data' => ['description' => 'test']]))
        )
        ->createOne(['name' => 'Test Collection']);

    $taxonomy = $collection->taxonomies->first();
    $taxonomyTerm = $taxonomy->taxonomyTerms->first();

    $collectionEntry = livewire(CreateCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey()])
        ->assertOk()
        ->fillForm([
            'title' => 'Test',
            'slug' => 'test',
            "taxonomies.{$taxonomy->getKey()}" => [$taxonomyTerm->getKey()],
            'data' => ['main' => ['header' => 'Foo']],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->instance()
        ->record;

    assertDatabaseHas(
        CollectionEntry::class,
        [
            'id' => $collection->id,
            'title' => 'Test',
            'slug' => 'test',
            'data' => json_encode(['main' => ['header' => 'Foo']]),
        ]
    );
    assertDatabaseHas(
        'collection_entry_taxonomy_term',
        [
            'taxonomy_term_id' => $taxonomyTerm->getKey(),
            'collection_entry_id' => $collectionEntry->getKey(),
        ]
    );
});

it('can create collection entry with publish date', function () {
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
});
