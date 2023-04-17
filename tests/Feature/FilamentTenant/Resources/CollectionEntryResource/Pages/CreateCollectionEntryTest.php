<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\CollectionEntryResource\Pages\CreateCollectionEntry;
use Carbon\Carbon;
use Domain\Collection\Models\CollectionEntry;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Support\MetaData\Models\MetaData;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can create collection entry', function () {
    /** @var \Domain\Collection\Models\Collection $collection */
    $collection = CollectionFactory::new(['name' => 'Test Collection'])
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne();

    /** @var CollectionEntry $collectionEntry */
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
    assertDatabaseHas(
        MetaData::class,
        [
            'title' => $collectionEntry->title,
            'model_type' => $collectionEntry->getMorphClass(),
            'model_id' => $collectionEntry->getKey(),
        ]
    );
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $collectionEntry->getMorphClass(),
        'model_id' => $collectionEntry->id,
        'url' => '/'.$collectionEntry->collection->prefix.'/'.\Illuminate\Support\Str::slug($collectionEntry->title),
        'is_override' => false,
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

it('can create collection entry with meta data', function () {
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

    $metaData = [
        'title' => 'Test Meta Data Title',
        'keywords' => 'Test Meta Data Keywords',
        'author' => 'Test Meta Data Author',
        'description' => 'Test Meta Data Description',
    ];
    $metaDataImage = UploadedFile::fake()->image('preview.jpeg');

    $collectionEntry = livewire(CreateCollectionEntry::class, ['ownerRecord' => $collection->getRouteKey()])
        ->assertOk()
        ->fillForm([
            'title' => 'Test',
            'slug' => 'test',
            'published_at' => $dateTime,
            'data' => ['main' => ['header' => 'Foo']],
            'meta_data' => $metaData,
            'meta_data.image.0' => $metaDataImage,
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
            'published_at' => $dateTime,
            'data' => json_encode(['main' => ['header' => 'Foo']]),
        ]
    );
    assertDatabaseHas(
        MetaData::class,
        array_merge(
            $metaData,
            [
                'model_type' => $collectionEntry->getMorphClass(),
                'model_id' => $collectionEntry->id,
            ]
        )
    );
    assertDatabaseHas(Media::class, [
        'file_name' => $metaDataImage->getClientOriginalName(),
        'mime_type' => $metaDataImage->getMimeType(),
    ]);
});
