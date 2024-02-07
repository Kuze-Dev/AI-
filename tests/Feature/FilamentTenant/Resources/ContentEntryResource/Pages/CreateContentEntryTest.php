<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\ContentEntryResource\Pages\CreateContentEntry;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Content\Database\Factories\ContentFactory;
use Domain\Content\Models\ContentEntry;
use Domain\Internationalization\Database\Factories\LocaleFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\MetaData\Models\MetaData;
use Support\RouteUrl\Models\RouteUrl;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();

    LocaleFactory::createDefault();
});

it('can create content entry', function () {
    $content = ContentFactory::new(['name' => 'Test Content'])
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne();

    $contentEntry = livewire(CreateContentEntry::class, ['ownerRecord' => $content->getRouteKey()])
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
        ContentEntry::class,
        [
            'id' => $content->id,
            'title' => 'Test',
            'slug' => 'test',
            'author_id' => auth()->user()->id,
            'data' => json_encode(['main' => ['header' => 'Foo']]),
        ]
    );
    assertDatabaseHas(
        MetaData::class,
        [
            'title' => $contentEntry->title,
            'model_type' => $contentEntry->getMorphClass(),
            'model_id' => $contentEntry->getKey(),
        ]
    );
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $contentEntry->getMorphClass(),
        'model_id' => $contentEntry->getKey(),
        'url' => ContentEntry::generateRouteUrl($contentEntry, $contentEntry->toArray()),
        'is_override' => false,
    ]);
});

it('can create content entry with taxonomy terms', function () {
    $content = ContentFactory::new(['name' => 'Test Content'])
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
        ->createOne(['name' => 'Test Content']);

    $taxonomy = $content->taxonomies->first();
    $taxonomyTerm = $taxonomy->taxonomyTerms->first();

    $contentEntry = livewire(CreateContentEntry::class, ['ownerRecord' => $content->getRouteKey()])
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
        ContentEntry::class,
        [
            'id' => $content->id,
            'title' => 'Test',
            'slug' => 'test',
            'data' => json_encode(['main' => ['header' => 'Foo']]),
        ]
    );
    assertDatabaseHas(
        'content_entry_taxonomy_term',
        [
            'taxonomy_term_id' => $taxonomyTerm->getKey(),
            'content_entry_id' => $contentEntry->getKey(),
        ]
    );
});

it('can create content entry with publish date', function () {
    $content = ContentFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne([
            'name' => 'Test Content',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    livewire(CreateContentEntry::class, ['ownerRecord' => $content->getRouteKey()])
        ->assertOk()
        ->fillForm([
            'title' => 'Test',
            'slug' => 'test',
            'published_at' => ($publishedAt = now(Auth::user()->timezone)->toImmutable()),
            'data' => ['main' => ['header' => 'Foo']],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(
        ContentEntry::class,
        [
            'id' => $content->id,
            'title' => 'Test',
            'slug' => 'test',
            'published_at' => $publishedAt,
            'data' => json_encode(['main' => ['header' => 'Foo']]),
        ]
    );
});

it('can create content entry with meta data', function () {
    $content = ContentFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne([
            'name' => 'Test Content',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    $metaData = [
        'title' => 'Test Meta Data Title',
        'keywords' => 'Test Meta Data Keywords',
        'author' => 'Test Meta Data Author',
        'description' => 'Test Meta Data Description',
    ];
    $metaDataImage = UploadedFile::fake()->image('preview.jpeg');

    $contentEntry = livewire(CreateContentEntry::class, ['ownerRecord' => $content->getRouteKey()])
        ->assertOk()
        ->fillForm([
            'title' => 'Test',
            'slug' => 'test',
            'data' => ['main' => ['header' => 'Foo']],
            'meta_data' => $metaData,
            'meta_data.image.0' => $metaDataImage,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->instance()
        ->record;

    assertDatabaseHas(
        ContentEntry::class,
        [
            'id' => $content->id,
            'title' => 'Test',
            'slug' => 'test',
            'data' => json_encode(['main' => ['header' => 'Foo']]),
        ]
    );
    assertDatabaseHas(
        MetaData::class,
        array_merge(
            $metaData,
            [
                'model_type' => $contentEntry->getMorphClass(),
                'model_id' => $contentEntry->getKey(),
            ]
        )
    );
    assertDatabaseHas(Media::class, [
        'file_name' => $metaDataImage->getClientOriginalName(),
        'mime_type' => $metaDataImage->getMimeType(),
    ]);
});

it('can create content entry with custom url', function () {
    $content = ContentFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne();

    $contentEntry = livewire(CreateContentEntry::class, ['ownerRecord' => $content->getRouteKey()])
        ->assertOk()
        ->fillForm([
            'title' => 'Test',
            'route_url' => [
                'is_override' => true,
                'url' => '/some/custom/url',
            ],
            'data' => ['main' => ['header' => 'Foo']],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->instance()
        ->record;

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $contentEntry->getMorphClass(),
        'model_id' => $contentEntry->getKey(),
        'url' => '/some/custom/url',
        'is_override' => true,
    ]);
});
