<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\EditPage;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\SliceFactory;
use Domain\Page\Models\Page;
use Domain\Page\Models\SliceContent;
use Domain\Support\MetaData\Models\MetaData;
use Domain\Support\SlugHistory\SlugHistory;
use Filament\Facades\Filament;

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
        ->addSliceContent(
            SliceFactory::new()
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'Main'])
                        ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
                ),
            ['data' => ['main' => ['header' => 'Foo']]]
        )
        ->createOne();

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => $page->name,
            'slice_contents.record-1' => $page->sliceContents->first()->toArray(),
        ])
        ->assertOk();
});

it('can edit page', function () {
    $page = PageFactory::new()
        ->addSliceContent(
            SliceFactory::new()
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

    $updatedMetaDataData = [
        'title' => 'Foo title updated',
        'description' => 'Foo description updated',
        'author' => 'Foo author updated',
        'keywords' => 'Foo keywords updated',
    ];

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'name' => 'Test',
            'route_url' => 'test-url',
            'slice_contents.record-1.data.main.header' => 'Bar',
            'meta_data' => $updatedMetaDataData,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Page::class, [
        'name' => 'Test',
        'route_url' => 'test-url',
    ]);

    assertDatabaseHas(
        MetaData::class,
        array_merge(
            $updatedMetaDataData,
            [
                'taggable_type' => $page->getMorphClass(),
                'taggable_id' => $page->id,
            ]
        )
    );

    assertDatabaseHas(SliceContent::class, [
        'page_id' => $page->id,
        'slice_id' => $page->sliceContents->first()->slice_id,
        'data' => json_encode(['main' => ['header' => 'Bar']]),
    ]);
});

it('can edit page slug', function () {
    $page = PageFactory::new(['slug' => 'foo'])
        ->addSliceContent(
            SliceFactory::new()
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
