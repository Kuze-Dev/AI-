<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\EditPage;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\SliceFactory;
use Domain\Page\Models\Page;
use Domain\Page\Models\SliceContent;
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

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'name' => 'Test',
            'route_url' => 'test-url',
            'slice_contents.record-1.data.main.header' => 'Bar',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseHas(Page::class, [
        'name' => 'Test',
        'route_url' => 'test-url',
    ]);
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

    livewire(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'slug' => 'new-foo',
            'slice_contents.record-1.data.main.header' => 'Bar',
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

it('page slice with default value will fill the slices fields', function () {
    $page = PageFactory::new(['slug' => 'foo'])
        ->addSliceContent(
            SliceFactory::new(
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
            'slice_contents.record-1.data.main.header' => 'Foo',
        ]);
});

it('page slice with default value column data must be dehydrated', function () {
    $page = PageFactory::new(['slug' => 'foo'])
        ->addSliceContent(
            SliceFactory::new(
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

    assertDatabaseHas(SliceContent::class, [
        'page_id' => $page->id,
        'data' => null,
    ]);
});
