<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\CreatePage;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\SliceFactory;
use Domain\Page\Models\Page;
use Domain\Page\Models\SliceContent;
use Domain\Support\MetaData\Models\MetaData;
use Domain\Support\SlugHistory\SlugHistory;
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
    $sliceId = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    $page = livewire(CreatePage::class)
        ->fillForm([
            'name' => 'Test',
            'route_url' => 'test-url',
            'slice_contents' => [
                [
                    'slice_id' => $sliceId,
                    'data' => ['name' => 'foo'],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Page::class, ['name' => 'Test']);
    assertDatabaseHas(SliceContent::class, [
        'page_id' => $page->id,
        'slice_id' => $sliceId,
        'data' => json_encode(['name' => 'foo']),
    ]);
    assertDatabaseHas(
        MetaData::class,
        [
            'title' => $page->name,
            'model_type' => $page->getMorphClass(),
            'model_id' => $page->getKey(),
        ]
    );
    assertDatabaseHas(SlugHistory::class, [
        'model_type' => $page->getMorphClass(),
        'model_id' => $page->id,
    ]);
});

it('can not create page with same name', function () {
    $sliceId = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    PageFactory::new()
        ->createOne(['name' => 'page 1']);

    assertDatabaseCount(Page::class, 1);

    livewire(CreatePage::class)
        ->fillForm([
            'name' => 'page 1',
            'slice_contents' => [
                [
                    'slice_id' => $sliceId,
                    'data' => ['name' => 'foo'],
                ],
            ],
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'unique'])
        ->assertOk();

    assertDatabaseCount(Page::class, 1);
});

it('can create page with meta data', function () {
    $sliceId = SliceFactory::new()
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
            'route_url' => 'test-url',
            'slice_contents' => [
                [
                    'slice_id' => $sliceId,
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
    assertDatabaseHas(SliceContent::class, [
        'page_id' => $page->id,
        'slice_id' => $sliceId,
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
    assertDatabaseHas(SlugHistory::class, [
        'model_type' => $page->getMorphClass(),
        'model_id' => $page->id,
    ]);
});

it('newly created page must have author_id', function () {
    $sliceId = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    $page = livewire(CreatePage::class)
        ->fillForm([
            'name' => 'Test',
            'route_url' => 'test-url',
            'slice_contents' => [
                [
                    'slice_id' => $sliceId,
                    'data' => ['name' => 'foo'],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Page::class, ['name' => 'Test', 'author_id' => auth()->user()->id,]);
    
});
