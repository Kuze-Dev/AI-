<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\CreatePage;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Internationalization\Database\Factories\LocaleFactory;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\Enums\Visibility;
use Domain\Page\Models\Page;
use Domain\Page\Models\BlockContent;
use Support\MetaData\Database\Factories\MetaDataFactory;
use Support\MetaData\Models\MetaData;
use Support\RouteUrl\Models\RouteUrl;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();

    LocaleFactory::createDefault();
});

it('can render page', function () {
    livewire(CreatePage::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create page', function () {
    $block = BlockFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $page = livewire(CreatePage::class)
        ->fillForm([
            'name' => 'Test',
            'block_contents' => [
                [
                    'block_id' => $block->getKey(),
                    'data' => ['name' => 'foo'],
                ],
            ],
            'visibility' => 'public',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Page::class, [
        'author_id' => auth()->user()->id,
        'name' => 'Test',
        'visibility' => Visibility::PUBLIC->value,
    ]);

    assertDatabaseHas(BlockContent::class, [
        'page_id' => $page->id,
        'block_id' => $block->getKey(),
        'data' => json_encode($block->blockContents->first()->data),
    ]);

    assertDatabaseHas(
        MetaData::class,
        [
            'title' => $page->name,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->getKey(),
        ]
    );
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $page->getMorphClass(),
        'model_id' => $page->getKey(),
        'url' => Page::generateRouteUrl($page, $page->toArray()),
        'is_override' => false,
    ]);
});

it('can not create page with same name', function () {
    $blockId = BlockFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    PageFactory::new()
        ->createOne(['name' => 'page 1']);

    livewire(CreatePage::class)
        ->fillForm([
            'name' => 'page 1',
            'block_contents' => [
                [
                    'block_id' => $blockId,
                    'data' => ['name' => 'foo'],
                ],
            ],
            'visibility' => 'public',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique'])
        ->assertOk();
});

it('can clone page', function () {
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
        ->has(MetaDataFactory::new())
        ->createOne();

    Livewire::withQueryParams(['clone' => $page->slug]);

    $clonePage = livewire(CreatePage::class)
        ->assertFormSet([
            'visibility' => $page->visibility,
            'published_at' => $page->published_at,
            'block_contents' => $page->blockContents->toArray(),
            'meta_data' => [
                'author' => $page->metaData?->author,
                'description' => $page->metaData?->description,
                'keywords' => $page->metaData?->keywords,
            ],
        ])
        ->fillForm(['name' => 'Test Clone'])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Page::class, [
        'name' => 'Test Clone',
        'visibility' => $page->visibility->value,
        'published_at' => $page->published_at,
    ]);
    assertDatabaseHas(BlockContent::class, [
        'page_id' => $clonePage->id,
        'block_id' => $page->blockContents->first()->block_id,
        'data' => json_encode($page->blockContents->first()->data),
    ]);
    assertDatabaseHas(MetaData::class, [
        'model_id' => $clonePage->id,
        'model_type' => $clonePage->getMorphClass(),
        'description' => $page->metaData->description,
        'author' => $page->metaData->author,
        'keywords' => $page->metaData->keywords,
    ]);
});

it('can create page with meta data', function () {
    $blockId = BlockFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    $metaData = [
        'title' => 'Test Title',
        'keywords' => 'Test Keywords',
        'author' => 'Test Author',
        'description' => 'Test Description',
    ];
    $metaDataImage = UploadedFile::fake()->image('preview.jpeg');

    $page = livewire(CreatePage::class)
        ->fillForm([
            'name' => 'Test',
            'block_contents' => [
                [
                    'block_id' => $blockId,
                    'data' => ['name' => 'foo'],
                ],
            ],
            'meta_data' => $metaData,
            'meta_data.image.0' => $metaDataImage,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Page::class, ['name' => 'Test']);
    assertDatabaseHas(BlockContent::class, [
        'page_id' => $page->id,
        'block_id' => $blockId,
        'data' => json_encode(['name' => 'foo']),
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
});

it('can create page with published at date', function () {
    $blockId = BlockFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    $page = livewire(CreatePage::class)
        ->fillForm([
            'name' => 'Test',
            'published_at' => true,
            'visibility' => 'public',
            'block_contents' => [
                [
                    'block_id' => $blockId,
                    'data' => ['name' => 'foo'],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(
        Page::class,
        [
            'name' => 'Test',
            'published_at' => $page->published_at,
        ]
    );
});

it('can create page with custom url', function () {
    $blockId = BlockFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    $page = livewire(CreatePage::class)
        ->fillForm([
            'name' => 'Test',
            'route_url' => [
                'is_override' => true,
                'url' => '/some/custom/url',
            ],
            'block_contents' => [
                [
                    'block_id' => $blockId,
                    'data' => ['name' => 'foo'],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $page->getMorphClass(),
        'model_id' => $page->getKey(),
        'url' => '/some/custom/url',
        'is_override' => true,
    ]);
});

it('can create page with media uploaded', function () {
    $block = BlockFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'main'])
                ->addMediaSchemaField([
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
                ])
                ->createOne()
        )
        ->createOne();
    // Set up fake S3 storage

    Storage::fake('s3');

    $file_name = 'fake_image.png';
    // Create a fake file to upload
    $file = UploadedFile::fake()->create($file_name, 200, 'image/png');

    // Perform the upload to S3
    Storage::disk('s3')->put('/', $file);

    $page = PageFactory::new()
        ->addBlockContent(
            BlockFactory::new()
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'main'])
                        ->addMediaSchemaField([
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
                        ])
                        ->createOne()
                ),
            ['data' => ['main' => ['image' => [$file->hashName()]]]]
        )
        ->has(MetaDataFactory::new())
        ->createOne();

    $block_content = $page->blockContents->first();

    $blueprintData = BlueprintData::create([
        'blueprint_id' => $block_content->block->blueprint->getKey(),
        'model_id' => $block_content->getKey(),
        'model_type' => $block_content->getMorphClass(),
        'state_path' => 'main.image',
        'value' => $file->hashName(),
        'type' => 'media',
    ]);
    $blueprintData->addMediaFromDisk($blueprintData->value, 's3')
        ->toMediaCollection('blueprint_media');

    dd(Media::all());
    assertDatabaseHas(BlueprintData::class, [
        'blueprint_id' => $blueprintData->blueprint_id,
        'model_id' => $blueprintData->model_id,
        'model_type' => $blueprintData->model_type,
        'state_path' => $blueprintData->state_path,
        'value' => $blueprintData->value,
        'type' => $blueprintData->type,
    ]);

    $blueprint = $block_content->block->blueprint->first();

    $schema = $blueprint->schema;
    $conversions = $schema->sections[0]->fields[0]->conversions;

    assertDatabaseHas(Media::class, [
        'file_name' => $blueprintData->value,
        'generated_conversions' => $conversions[0]->name,

    ]);

})->only();
