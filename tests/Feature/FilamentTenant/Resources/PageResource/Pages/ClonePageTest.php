<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\ClonePage;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\Models\BlockContent;
use Domain\Page\Models\Page;
use Domain\Support\MetaData\Database\Factories\MetaDataFactory;
use Domain\Support\MetaData\Models\MetaData;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
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

    livewire(ClonePage::class, ['record' => $page->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'published_at' => true,
            'block_contents.record-1' => $page->blockContents->first()->toArray(),
        ])
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
        ->has(MetaDataFactory::new([
            'title' => 'Foo title',
            'description' => 'Foo description',
            'author' => 'Foo author',
            'keywords' => 'Foo keywords',
        ]))
        ->createOne([
            'visibility' => 'public',
        ]);

    $page->load(['blockContents', 'metaData', 'routeUrls']);

    $clonePage = livewire(ClonePage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'name' => 'Test',
            'published_at' => $page->published_at ? true : false,
            'block_contents.record-1.data.main.header' => $page->blockContents->first()->data['main']['header'],
            'meta_data' => $page->metaData(),
            'visibility' => $page->visibility->value,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Page::class, [
        'name' => 'Test',
        'visibility' => $clonePage->visibility->value,
        'published_at' => $clonePage->published_at,
    ]);

    assertDatabaseHas(
        MetaData::class,
        array_merge(
            $page->metaData->getAttributes(),
            [
                'model_type' => $page->getMorphClass(),
                'model_id' => $page->id,
            ]
        )
    );

    assertDatabaseHas(BlockContent::class, [
        'page_id' => $page->id,
        'block_id' => $page->blockContents->first()->block_id,
        'data' => json_encode($page->blockContents->first()->data),
    ]);
});

it('can clone page with custom url', function () {
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

    $page->load(['blockContents', 'metaData', 'routeUrls']);

    $clonePage = livewire(ClonePage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'name' => 'Test',
            'published_at' => $page->published_at ? true : false,
            'block_contents.record-1.data.main.header' => $page->blockContents->first()->data['main']['header'],
            'meta_data' => $page->metaData(),
            'visibility' => $page->visibility->value,
            'route_url' => [
                'is_override' => true,
                'url' => '/some/custom/url',
            ],
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;

    assertDatabaseHas(Page::class, [
        'name' => 'Test',
        'visibility' => $clonePage->visibility->value,
        'published_at' => $clonePage->published_at,
    ]);

    $clonePage->load(['routeUrls']);

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $clonePage->getMorphClass(),
        'model_id' => $clonePage->id,
        'url' => $clonePage->routeUrls[0]->url,
        'is_override' => $clonePage->routeUrls[0]->is_override,
    ]);
});
