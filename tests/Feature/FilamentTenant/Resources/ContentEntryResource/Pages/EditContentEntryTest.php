<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\ContentEntryResource\Pages\EditContentEntry;
use Carbon\Carbon;
use Domain\Content\Models\ContentEntry;
use Domain\Content\Database\Factories\ContentFactory;
use Domain\Content\Database\Factories\ContentEntryFactory;
use Support\RouteUrl\Models\RouteUrl;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Support\MetaData\Database\Factories\MetaDataFactory;
use Support\MetaData\Models\MetaData;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;

use Spatie\MediaLibrary\MediaCollections\Models\Media;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render content entry', function () {
    $content = ContentFactory::new()
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
            'name' => 'Test Content',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    $contentEntry = ContentEntryFactory::new()
        ->for($content)
        ->has(
            TaxonomyTermFactory::new(['data' => ['description' => 'test']])
                ->for($content->taxonomies->first())
                ->count(2)
        )
        ->createOne([
            'title' => 'Foo',
            'published_at' => Carbon::now(),
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    livewire(EditContentEntry::class, ['ownerRecord' => $content->getRouteKey(), 'record' => $contentEntry->getRouteKey()])
        ->assertFormSet([
            'title' => $contentEntry->title,
            'published_at' => (string) $contentEntry->published_at->timezone(Auth::user()->timezone),
            'data' => $contentEntry->data,
            'taxonomies' => [
                $content->taxonomies->first()->id => $contentEntry->taxonomyTerms->pluck('id')->toArray(),
            ],
        ]);
});

it('can edit content entry', function () {
    $content = ContentFactory::new()
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
            'name' => 'Test Content',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    $contentEntry = ContentEntryFactory::new()
        ->for($content)
        ->has(
            TaxonomyTermFactory::new(['data' => ['description' => 'test']])
                ->for($content->taxonomies->first())
                ->count(2)
        )
        ->has(MetaDataFactory::new(['title' => 'Foo']))
        ->createOne([
            'title' => 'Foo',
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    $dateTime = Carbon::now();

    $updatedContentEntry = livewire(EditContentEntry::class, ['ownerRecord' => $content->getRouteKey(), 'record' => $contentEntry->getRouteKey()])
        ->fillForm([
            'title' => 'New Foo',
            'published_at' => $publishedAt = now(Auth::user()?->timezone)->toImmutable(),
            'data' => ['main' => ['header' => 'Foo updated']],
            'taxonomies' => [
                $content->taxonomies->first()->id => $contentEntry->taxonomyTerms->pluck('id'),
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

    assertDatabaseHas(ContentEntry::class, [
        'title' => 'New Foo',
        'published_at' => $publishedAt,
        'data' => json_encode(['main' => ['header' => 'Foo updated']]),
    ]);

    assertDatabaseHas(
        MetaData::class,
        [
            'title' => 'New Foo',
            'description' => null,
            'author' => null,
            'keywords' => null,
            'model_type' => $updatedContentEntry->getMorphClass(),
            'model_id' => $updatedContentEntry->getKey(),
        ]
    );

    foreach ($contentEntry->taxonomyTerms as $taxonomyTerm) {
        assertDatabaseHas('content_entry_taxonomy_term', [
            'taxonomy_term_id' => $taxonomyTerm->getKey(),
            'content_entry_id' => $contentEntry->getKey(),
        ]);
    }

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $contentEntry->getMorphClass(),
        'model_id' => $contentEntry->getKey(),
        'url' => ContentEntry::generateRouteUrl($contentEntry, $updatedContentEntry->toArray()),
        'is_override' => false,
    ]);
});

it('can edit content entry with custom url', function () {
    $content = ContentFactory::new(['name' => 'Test Content'])
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne();

    $contentEntry = ContentEntryFactory::new()
        ->for($content)
        ->createOne([
            'title' => 'Foo',
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    livewire(EditContentEntry::class, ['ownerRecord' => $content->getRouteKey(), 'record' => $contentEntry->getRouteKey()])
        ->fillForm([
            'route_url' => [
                'is_override' => true,
                'url' => '/some/custom/url',
            ],
        ])
        ->call('save')
        ->assertOk()
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

it('can edit content entry to have no taxonomy terms attached', function () {
    $content = ContentFactory::new()
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

    $contentEntry = ContentEntryFactory::new()
        ->for($content)
        ->has(
            TaxonomyTermFactory::new(['data' => ['description' => 'test']])
                ->for($content->taxonomies->first())
                ->count(2)
        )
        ->createOne([
            'title' => 'Foo',
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    livewire(EditContentEntry::class, ['ownerRecord' => $content->getRouteKey(), 'record' => $contentEntry->getRouteKey()])
        ->fillForm([
            'taxonomies' => [
                $content->taxonomies->first()->id => [],
            ],
        ])
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors();

    assertDatabaseMissing('content_entry_taxonomy_term', ['content_entry_id' => $contentEntry->getKey()]);
});

it('can edit content entry meta data', function () {
    $blueprint = BlueprintFactory::new()
        ->addSchemaSection(['title' => 'Main'])
        ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT]);

    $content = ContentFactory::new()
        ->for($blueprint)
        ->has(TaxonomyFactory::new()->for($blueprint))
        ->createOne([
            'name' => 'Test Content',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    $taxonomyTerms = TaxonomyTermFactory::new()
        ->for($content->taxonomies->first())
        ->count(2)
        ->create();

    $contentEntry = ContentEntryFactory::new()
        ->for($content)
        ->has(MetaDataFactory::new(['title' => 'Foo']))
        ->createOne([
            'title' => 'Foo',
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    $metaData = [
        'title' => 'Updated foo title',
        'description' => 'Updated foo description',
        'author' => 'Updated foo author',
        'keywords' => 'Updated foo keywords',
    ];
    $metaDataImage = UploadedFile::fake()->image('preview.jpeg');

    $updatedContentEntry = livewire(EditContentEntry::class, ['ownerRecord' => $content->getRouteKey(), 'record' => $contentEntry->getRouteKey()])
        ->fillForm([
            'title' => 'Updated Foo',
            'slug' => 'updated-foo',
            'data' => ['main' => ['header' => 'Foo updated']],
            'taxonomies' => [
                $content->taxonomies->first()->id => $taxonomyTerms->pluck('id'),
            ],
            'meta_data' => $metaData,
            'meta_data.image.0' => $metaDataImage,
        ])
        ->call('save')
        ->assertOk()
        ->assertHasNoFormErrors()
        ->instance()
        ->record;

    assertDatabaseHas(ContentEntry::class, [
        'title' => 'Updated Foo',
        'data' => json_encode(['main' => ['header' => 'Foo updated']]),
    ]);

    assertDatabaseHas(
        MetaData::class,
        array_merge(
            $metaData,
            [
                'model_type' => $updatedContentEntry->getMorphClass(),
                'model_id' => $updatedContentEntry->getKey(),
            ]
        )
    );

    assertDatabaseHas(Media::class, [
        'file_name' => $metaDataImage->getClientOriginalName(),
        'mime_type' => $metaDataImage->getMimeType(),
    ]);

    foreach ($taxonomyTerms as $taxonomyTerm) {
        assertDatabaseHas('content_entry_taxonomy_term', [
            'taxonomy_term_id' => $taxonomyTerm->getKey(),
            'content_entry_id' => $contentEntry->getKey(),
        ]);
    }
});

it('can create content entry draft', function () {
    $content = ContentFactory::new()
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
            'name' => 'Test Content',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    $contentEntry = ContentEntryFactory::new()
        ->for($content)
        ->has(
            TaxonomyTermFactory::new(['data' => ['description' => 'test']])
                ->for($content->taxonomies->first())
                ->count(2)
        )
        ->has(MetaDataFactory::new(['title' => 'Foo']))
        ->createOne([
            'title' => 'Foo',
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    $dateTime = Carbon::now();

    $updatedContentEntry = livewire(EditContentEntry::class, ['ownerRecord' => $content->getRouteKey(), 'record' => $contentEntry->getRouteKey()])
        ->fillForm([
            'title' => 'New Foo',
            'published_at' => $publishedAt = now(Auth::user()?->timezone)->toImmutable(),
            'data' => ['main' => ['header' => 'Foo updated']],
            'taxonomies' => [
                $content->taxonomies->first()->id => $contentEntry->taxonomyTerms->pluck('id'),
            ],
            'meta_data' => [
                'title' => '',
                'description' => '',
                'author' => '',
                'keywords' => '',
            ],
        ])
        ->call('draft')
        ->assertOk()
        ->assertHasNoFormErrors()
        ->instance()
        ->record;

        $contentEntryDraft = $contentEntry->pageDraft;

    assertDatabaseHas(ContentEntry::class, [
        'title' => 'New Foo',
        'published_at' => $publishedAt,
        'data' => json_encode(['main' => ['header' => 'Foo updated']]),
    ]);

    assertDatabaseHas(
        MetaData::class,
        [
            'title' => 'New Foo',
            'description' => null,
            'author' => null,
            'keywords' => null,
            'model_type' => $contentEntryDraft->getMorphClass(),
            'model_id' => $contentEntryDraft->getKey(),
        ]
    );

    foreach ($contentEntry->taxonomyTerms as $taxonomyTerm) {
        assertDatabaseHas('content_entry_taxonomy_term', [
            'taxonomy_term_id' => $taxonomyTerm->getKey(),
            'content_entry_id' => $contentEntry->getKey(),
        ]);
    }

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $contentEntryDraft->getMorphClass(),
        'model_id' => $contentEntryDraft->getKey(),
        'url' => ContentEntry::generateRouteUrl($contentEntryDraft, $contentEntryDraft->toArray()),
        'is_override' => false,
    ]);
});


it('can overwrite content entry existing draft', function () {
    $content = ContentFactory::new()
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
            'name' => 'Test Content',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ]);

    $contentEntry = ContentEntryFactory::new()
        ->for($content)
        ->has(
            TaxonomyTermFactory::new(['data' => ['description' => 'test']])
                ->for($content->taxonomies->first())
                ->count(2)
        )
        ->has(MetaDataFactory::new(['title' => 'Foo']))
        ->createOne([
            'title' => 'Foo',
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    $initialContentEntryDraft = ContentEntryFactory::new()
        ->for($content)
        ->has(
            TaxonomyTermFactory::new(['data' => ['description' => 'test']])
                ->for($content->taxonomies->first())
                ->count(2)
        )
        ->has(MetaDataFactory::new(['title' => 'Foo']))
        ->createOne([
            'title' => 'Foo',
            'draftable_id' => $contentEntry->getKey(),
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    $dateTime = Carbon::now();

    $updatedContentEntry = livewire(EditContentEntry::class, ['ownerRecord' => $content->getRouteKey(), 'record' => $initialContentEntryDraft->getRouteKey()])
        ->fillForm([
            'title' => 'New Foo',
            'published_at' => $publishedAt = now(Auth::user()?->timezone)->toImmutable(),
            'data' => ['main' => ['header' => 'Foo updated']],
            'taxonomies' => [
                $content->taxonomies->first()->id => $contentEntry->taxonomyTerms->pluck('id'),
            ],
            'meta_data' => [
                'title' => '',
                'description' => '',
                'author' => '',
                'keywords' => '',
            ],
        ])
        ->call('overwriteDraft')
        ->assertOk()
        ->assertHasNoFormErrors()
        ->instance()
        ->record;

        $contentEntryDraft = $contentEntry->pageDraft;

    assertDatabaseMissing(ContentEntry::class, [
        'name' => $initialContentEntryDraft->name,
        'draftable_id' => $contentEntry->id,
    ]);

    assertDatabaseHas(ContentEntry::class, [
        'title' => 'New Foo',
        'published_at' => $publishedAt,
        'data' => json_encode(['main' => ['header' => 'Foo updated']]),
    ]);

    assertDatabaseHas(
        MetaData::class,
        [
            'title' => 'New Foo',
            'description' => null,
            'author' => null,
            'keywords' => null,
            'model_type' => $contentEntryDraft->getMorphClass(),
            'model_id' => $contentEntryDraft->getKey(),
        ]
    );

    foreach ($contentEntry->taxonomyTerms as $taxonomyTerm) {
        assertDatabaseHas('content_entry_taxonomy_term', [
            'taxonomy_term_id' => $taxonomyTerm->getKey(),
            'content_entry_id' => $contentEntry->getKey(),
        ]);
    }

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $contentEntryDraft->getMorphClass(),
        'model_id' => $contentEntryDraft->getKey(),
        'url' => ContentEntry::generateRouteUrl($contentEntryDraft, $contentEntryDraft->toArray()),
        'is_override' => false,
    ]);
})->only();

