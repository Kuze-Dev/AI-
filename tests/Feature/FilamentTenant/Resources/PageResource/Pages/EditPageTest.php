<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\CreatePage;
use App\FilamentTenant\Resources\PageResource\Pages\EditPage;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Internationalization\Database\Factories\LocaleFactory;
use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Enums\Visibility;
use Domain\Page\Models\BlockContent;
use Domain\Page\Models\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\MetaData\Database\Factories\MetaDataFactory;
use Support\MetaData\Models\MetaData;
use Support\RouteUrl\Models\RouteUrl;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
    LocaleFactory::createDefault();
});

it('can render page', function () {
    $page = PageFactory::new()
        ->published()
        ->addBlockContent(
            BlockFactory::new()
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'Main'])
                        ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
                ),
            ['data' => ['main' => ['header' => 'Foo']]]
        )
        ->createOne([
            'visibility' => 'public',
        ]);

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => $page->name,
            'published_at' => true,
            'block_contents.record-1' => $page->blockContents->first()->toArray(),
        ])
        ->assertOk();
});

it('can edit page', function () {
    $page = PageFactory::new()
        ->addBlockContent(
            BlockFactory::new()
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'Main'])
                        ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
                ),
            ['data' => ['main' => ['header' => 'Foo']]]
        )
        ->has(MetaDataFactory::new([
            'title' => 'Foo title',
            'description' => 'Foo description',
            'author' => 'Foo author',
            'keywords' => 'Foo keywords',
        ]))
        ->createOne([
            'visibility' => 'public',
        ]);

    $metaData = [
        'title' => 'Foo title updated',
        'description' => 'Foo description updated',
        'author' => 'Foo author updated',
        'keywords' => 'Foo keywords updated',
    ];
    $metaDataImage = UploadedFile::fake()->image('preview.jpeg');

    $updatedPage = livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'name' => 'Test',
            'published_at' => true,
            'block_contents.record-1.data.main.header' => 'Bar',
            'meta_data' => $metaData,
            'visibility' => 'authenticated',
            'meta_data.image.0' => $metaDataImage,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Page::class, [
        'name' => 'Test',
        'visibility' => Visibility::AUTHENTICATED->value,
        'published_at' => $updatedPage->published_at,
    ]);

    assertDatabaseHas(
        MetaData::class,
        array_merge(
            $metaData,
            [
                'model_type' => $page->getMorphClass(),
                'model_id' => $page->getKey(),
            ]
        )
    );

    assertDatabaseHas(Media::class, [
        'file_name' => $metaDataImage->getClientOriginalName(),
        'mime_type' => $metaDataImage->getMimeType(),
    ]);

    assertDatabaseHas(BlockContent::class, [
        'page_id' => $page->id,
        'block_id' => $page->blockContents->first()->block_id,
        'data' => json_encode(['main' => ['header' => 'Bar']]),
    ]);

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $page->getMorphClass(),
        'model_id' => $page->getKey(),
        'url' => Page::generateRouteUrl($page, $updatedPage->toArray()),
        'is_override' => false,
    ]);
});

it('can edit page with custom url', function () {
    $page = PageFactory::new(['slug' => 'foo'])
        ->addBlockContent(
            BlockFactory::new()
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'Main'])
                        ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
                ),
            ['data' => ['main' => ['header' => 'Foo']]]
        )
        ->createOne([
            'visibility' => 'public',
        ]);

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'route_url' => [
                'is_override' => true,
                'url' => '/some/custom/url',
            ],
            'block_contents.record-1.data.main.header' => 'Bar',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $page->getMorphClass(),
        'model_id' => $page->getKey(),
        'url' => '/some/custom/url',
        'is_override' => true,
    ]);
});

it('page block with default value will fill the blocks fields', function () {
    $page = PageFactory::new()
        ->addBlockContent(
            BlockFactory::new(
                [
                    'is_fixed_content' => true,
                    'data' => ['main' => ['header' => 'Foo']],
                ]
            )
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'Main'])
                        ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
                ),
            ['data' => null]
        )
        ->createOne([
            'visibility' => 'public',
        ]);

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'slug' => 'new-foo',
        ])
        ->assertHasNoFormErrors()
        ->assertOk()
        ->assertFormSet([
            'block_contents.record-1.data.main.header' => 'Foo',
        ]);
});

it('page block with default value column data must be dehydrated', function () {
    $page = PageFactory::new(['slug' => 'foo'])
        ->addBlockContent(
            BlockFactory::new(
                [
                    'is_fixed_content' => true,
                    'data' => ['main' => ['header' => 'Foo']],
                ]
            )
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'Main'])
                        ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
                ),
            ['data' => null]
        )
        ->createOne([
            'visibility' => 'public',
        ]);

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(BlockContent::class, [
        'page_id' => $page->id,
        'data' => null,
    ]);
});

it('can create page draft', function () {
    $page = PageFactory::new()
        ->addBlockContent(
            BlockFactory::new()
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'Main'])
                        ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
                ),
            ['data' => ['main' => ['header' => 'Foo']]]
        )
        ->has(MetaDataFactory::new([
            'title' => 'Foo title',
            'description' => 'Foo description',
            'author' => 'Foo author',
            'keywords' => 'Foo keywords',
        ]))
        ->createOne([
            'visibility' => 'public',
        ]);

    $metaData = [
        'title' => 'Foo title updated',
        'description' => 'Foo description updated',
        'author' => 'Foo author updated',
        'keywords' => 'Foo keywords updated',
    ];
    $metaDataImage = UploadedFile::fake()->image('preview.jpeg');

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'name' => 'Test',
            'block_contents.record-1.data.main.header' => 'Bar',
            'meta_data' => $metaData,
            'visibility' => 'authenticated',
            'meta_data.image.0' => $metaDataImage,
        ])
        ->call('draft')
        ->assertHasNoFormErrors()
        ->assertOk();

    $pageDraft = $page->pageDraft;

    assertDatabaseHas(Page::class, [
        'name' => 'Test',
        'visibility' => Visibility::AUTHENTICATED->value,
        'draftable_id' => $page->id,
    ]);

    assertDatabaseHas(
        MetaData::class,
        array_merge(
            $metaData,
            [
                'model_type' => $pageDraft->getMorphClass(),
                'model_id' => $pageDraft->getKey(),
            ]
        )
    );

    assertDatabaseHas(Media::class, [
        'file_name' => $metaDataImage->getClientOriginalName(),
        'mime_type' => $metaDataImage->getMimeType(),
    ]);

    assertDatabaseHas(BlockContent::class, [
        'page_id' => $pageDraft->id,
        'block_id' => $pageDraft->blockContents->first()->block_id,
        'data' => json_encode(['main' => ['header' => 'Bar']]),
    ]);

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $pageDraft->getMorphClass(),
        'model_id' => $pageDraft->getKey(),
        'url' => Page::generateRouteUrl($pageDraft, $pageDraft->toArray()),
        'is_override' => false,
    ]);
});

it('can overwrite page draft', function () {
    $page = PageFactory::new()
        ->addBlockContent(
            BlockFactory::new()
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'Main'])
                        ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
                ),
            ['data' => ['main' => ['header' => 'Foo']]]
        )
        ->has(MetaDataFactory::new([
            'title' => 'Foo title',
            'description' => 'Foo description',
            'author' => 'Foo author',
            'keywords' => 'Foo keywords',
        ]))
        ->createOne([
            'visibility' => 'public',
        ]);

    $metaData = [
        'title' => 'Foo title updated',
        'description' => 'Foo description updated',
        'author' => 'Foo author updated',
        'keywords' => 'Foo keywords updated',
    ];
    $metaDataImage = UploadedFile::fake()->image('preview.jpeg');

    $initialDraft = $page->pageDraft()->create([
        'name' => $page->name.'v2',
        'visibility' => Visibility::AUTHENTICATED->value,
    ]);

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'name' => 'overwrite draft v2',
            'block_contents.record-1.data.main.header' => 'Bar',
            'meta_data' => $metaData,
            'visibility' => 'authenticated',
            'meta_data.image.0' => $metaDataImage,
        ])
        ->call('overwriteDraft')
        ->assertHasNoFormErrors()
        ->assertOk();

    $pageDraft = $page->pageDraft;

    assertDatabaseMissing(Page::class, [
        'name' => $initialDraft->name,
        'visibility' => Visibility::AUTHENTICATED->value,
        'draftable_id' => $page->id,
    ]);

    assertDatabaseHas(Page::class, [
        'name' => 'overwrite draft v2',
        'visibility' => Visibility::AUTHENTICATED->value,
        'draftable_id' => $page->id,
    ]);

    assertDatabaseHas(
        MetaData::class,
        array_merge(
            $metaData,
            [
                'model_type' => $pageDraft->getMorphClass(),
                'model_id' => $pageDraft->getKey(),
            ]
        )
    );

    assertDatabaseHas(Media::class, [
        'file_name' => $metaDataImage->getClientOriginalName(),
        'mime_type' => $metaDataImage->getMimeType(),
    ]);

    assertDatabaseHas(BlockContent::class, [
        'page_id' => $pageDraft->id,
        'block_id' => $pageDraft->blockContents->first()->block_id,
        'data' => json_encode(['main' => ['header' => 'Bar']]),
    ]);

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $pageDraft->getMorphClass(),
        'model_id' => $pageDraft->getKey(),
        'url' => Page::generateRouteUrl($pageDraft, $pageDraft->toArray()),
        'is_override' => false,
    ]);
});

it('can published page draft', function () {
    $page = PageFactory::new()
        ->addBlockContent(
            BlockFactory::new()
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'Main'])
                        ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
                ),
            ['data' => ['main' => ['header' => 'Foo']]]
        )
        ->has(MetaDataFactory::new([
            'title' => 'Foo title',
            'description' => 'Foo description',
            'author' => 'Foo author',
            'keywords' => 'Foo keywords',
        ]))
        ->createOne([
            'visibility' => 'public',
        ]);

    $initialDraft = PageFactory::new([])
        ->addBlockContent(
            BlockFactory::new()
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'Main'])
                        ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
                ),
            ['data' => ['main' => ['header' => 'Foo']]]
        )
        ->has(MetaDataFactory::new([
            'title' => 'Foo title',
            'description' => 'Foo description',
            'author' => 'Foo author',
            'keywords' => 'Foo keywords',
        ]))
        ->createOne([
            'draftable_id' => $page->id,
            'visibility' => 'public',
        ]);

    $metaData = [
        'title' => 'Foo title updated',
        'description' => 'Foo description updated',
        'author' => 'Foo author updated',
        'keywords' => 'Foo keywords updated',
    ];
    $metaDataImage = UploadedFile::fake()->image('preview.jpeg');

    livewire(EditPage::class, ['record' => $initialDraft->getRouteKey()])
        ->fillForm([
            'name' => 'published draft',
            'block_contents.record-2.data.main.header' => 'Bar',
            'meta_data' => $metaData,
            'visibility' => 'authenticated',
            'meta_data.image.0' => $metaDataImage,
        ])
        ->call('published')
        ->assertHasNoFormErrors()
        ->assertOk();

    $page->refresh();

    assertDatabaseMissing(Page::class, [
        'name' => $initialDraft->name,
        'visibility' => Visibility::AUTHENTICATED->value,
        'draftable_id' => $page->id,
    ]);

    assertDatabaseHas(Page::class, [
        'name' => 'published draft',
        'visibility' => Visibility::AUTHENTICATED->value,
    ]);

    assertDatabaseHas(
        MetaData::class,
        array_merge(
            $metaData,
            [
                'model_type' => $page->getMorphClass(),
                'model_id' => $page->getKey(),
            ]
        )
    );

    assertDatabaseHas(Media::class, [
        'file_name' => $metaDataImage->getClientOriginalName(),
        'mime_type' => $metaDataImage->getMimeType(),
    ]);

    assertDatabaseHas(BlockContent::class, [
        'page_id' => $page->id,
        'block_id' => $page->blockContents->first()->block_id,
        'data' => json_encode(['main' => ['header' => 'Bar']]),
    ]);

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $page->getMorphClass(),
        'model_id' => $page->getKey(),
        'url' => Page::generateRouteUrl($page, $page->toArray()),
        'is_override' => false,
    ]);
});

it('can edit page with media uploaded', function () {
    Storage::fake('s3');

    $firstImage = UploadedFile::fake()->image('preview-1.jpeg');

    Storage::disk('s3')->put('/', $firstImage);

    $page1 = PageFactory::new()
        ->addBlockContent(
            BlockFactory::new()
                ->for(
                    BlueprintFactory::new(['name' => 'imagex'])
                        ->addSchemaSection(['title' => 'main'])
                        ->addMediaSchemaField(
                            [
                                'title' => 'header',
                                'type' => FieldType::MEDIA,
                                'conversions' => [
                                    [
                                        'name' => 'desktop',
                                        'manipulations' => [
                                            'width' => 200,
                                            'height' => 200,
                                        ],
                                    ],
                                ],
                            ]
                        )
                ),
            ['data' => ['main' => ['header' => [$firstImage->hashName()]]]]
        )
        ->has(MetaDataFactory::new([
            'title' => 'Foo title',
            'description' => 'Foo description',
            'author' => 'Foo author',
            'keywords' => 'Foo keywords',
        ]))
        ->createOne([
            'visibility' => 'public',
        ]);

    $secondImage = UploadedFile::fake()->image('preview-2.jpeg');
    Storage::disk('s3')->put('/', $secondImage);

    $updatedPage = livewire(EditPage::class, ['record' => $page1->getRouteKey()])
        ->fillForm([
            'name' => 'Testxxx',
            'published_at' => true,
            'block_contents.record-1.data.main.header' => [$secondImage->hashName()],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Page::class, [
        'name' => 'Testxxx',
        'published_at' => $updatedPage->published_at,
    ]);

});

it('can edit page with media uploaded inside repeater', function () {
    $block = BlockFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'main'])
                ->addMediaSchemaField([
                    'title' => 'repeater',
                    'type' => FieldType::REPEATER,
                    'fields' => [
                        [
                            'title' => 'image',
                            'type' => FieldType::MEDIA,
                            'conversions' => [
                                [
                                    'name' => 'desktop',
                                    'manipulations' => [
                                        'width' => 200,
                                        'height' => 200,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ])
                ->createOne()
        )
        ->createOne();

    Storage::fake('s3');
    $firstImage = UploadedFile::fake()->image('preview.jpeg');
    Storage::disk('s3')->put('/', $firstImage);

    $page = livewire(CreatePage::class)
        ->fillForm([
            'name' => 'Test',
            'block_contents' => [
                [
                    'block_id' => $block->getKey(),
                    'data' => ['main' => ['repeater' => [['image' => [$firstImage->hashName()][0]]]]],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    $blockContent = $page->blockContents->first();
    $schema = $blockContent->block->blueprint->schema;

    assertDatabaseHas(Media::class, [
        'collection_name' => 'blueprint_media',
        'generated_conversions' => json_encode([$schema->sections[0]->fields[0]->fields[0]->conversions[0]->name => true]),

    ]);

    Storage::fake('s3');

    $secondImage = UploadedFile::fake()->image('preview.jpeg');

    Storage::disk('s3')->put('/', $secondImage);
    $updatedPage = livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'name' => 'Test',
            'block_contents' => [
                [
                    'block_id' => $block->getKey(),
                    'data' => ['main' => ['repeater' => [['image' => [$secondImage->hashName()][0]]]]],
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
    ->record;

    assertDatabaseHas(Page::class, [
        'name' => 'Test',
        'published_at' => $updatedPage->published_at,
    ]);

    assertDatabaseHas(Media::class, [
        'file_name' => $secondImage->hashName(),
    ]);
});
