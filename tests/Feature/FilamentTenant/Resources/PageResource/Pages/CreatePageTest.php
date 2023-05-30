<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\CreatePage;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\Enums\Visibility;
use Domain\Page\Models\Page;
use Domain\Page\Models\BlockContent;
use Domain\Support\MetaData\Models\MetaData;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
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
        'model_id' => $page->id,
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

    assertDatabaseCount(Page::class, 2);

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

    assertDatabaseCount(Page::class, 2);
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
                'model_id' => $page->id,
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
        'model_id' => $page->id,
        'url' => '/some/custom/url',
        'is_override' => true,
    ]);
});
