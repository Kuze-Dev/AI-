<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\EditPage;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Internationalization\Database\Factories\LocaleFactory;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\Enums\Visibility;
use Domain\Page\Models\BlockContent;
use Domain\Page\Models\Page;
use Support\MetaData\Database\Factories\MetaDataFactory;
use Support\MetaData\Models\MetaData;
use Support\RouteUrl\Models\RouteUrl;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;

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
