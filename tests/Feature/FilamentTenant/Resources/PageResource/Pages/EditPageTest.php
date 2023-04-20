<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\EditPage;
use Carbon\Carbon;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\Models\Page;
use Domain\Page\Models\BlockContent;
use Domain\Support\MetaData\Database\Factories\MetaDataFactory;
use Domain\Support\MetaData\Models\MetaData;
use Domain\Support\SlugHistory\SlugHistory;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
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
        ->createOne([
            'name' => 'Test',
            'published_at' => Carbon::now(),
        ]);

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => $page->name,
            'published_at' => (string) $page->published_at->timezone(Auth::user()->timezone),
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
        ->createOne();

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
            'route_url' => 'test-url',
            'published_at' => true,
            'block_contents.record-1.data.main.header' => 'Bar',
            'meta_data' => $metaData,
            'meta_data.image.0' => $metaDataImage,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Page::class, [
        'name' => 'Test',
        'route_url' => 'test-url',
        'published_at' => $updatedPage->published_at,
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

    assertDatabaseHas(BlockContent::class, [
        'page_id' => $page->id,
        'block_id' => $page->blockContents->first()->block_id,
        'data' => json_encode(['main' => ['header' => 'Bar']]),
    ]);
});

it('can edit page slug', function () {
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
        ->createOne();

    $metaDataData = [
        'title' => $page->slug,
        'description' => 'Foo description',
        'author' => 'Foo author',
        'keywords' => 'Foo keywords',
    ];

    $page->metaData()->create($metaDataData);

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'slug' => 'new-foo',
            'block_contents.record-1.data.main.header' => 'Bar',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Page::class, [
        'id' => $page->id,
        'slug' => 'new-foo',
    ]);
    assertDatabaseCount(SlugHistory::class, 2);
    assertDatabaseHas(SlugHistory::class, [
        'model_type' => $page->getMorphClass(),
        'model_id' => $page->id,
        'slug' => 'new-foo',
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
        ->createOne();

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
        ->createOne();

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'slug' => 'new-foo',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(BlockContent::class, [
        'page_id' => $page->id,
        'data' => null,
    ]);
});
