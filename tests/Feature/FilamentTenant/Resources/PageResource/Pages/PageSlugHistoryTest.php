<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\CreatePage;
use App\FilamentTenant\Resources\PageResource\Pages\EditPage;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\SliceFactory;
use Domain\Page\Models\Page;
use Filament\Facades\Filament;
use Illuminate\Support\Str;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('page has slug histories', function () {
    $sliceId = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    $pageName = 'Test Page';

    livewire(CreatePage::class)
        ->fillForm([
            'name' => 'Test page',
            'slice_contents' => [
                [
                    'slice_id' => $sliceId,
                    'data' => ['name' => 'foo'],
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    expect(
        Page::where('slug', Str::slug($pageName))->first()->sluggable
    )->toHaveCount(1);
});

it('can edit page slug', function () {
    $sliceId = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    $pageName = 'Test Page';

    livewire(CreatePage::class)
        ->fillForm([
            'name' => $pageName,
            'slice_contents' => [
                [
                    'slice_id' => $sliceId,
                    'data' => ['name' => 'foo'],
                ],
            ],
        ])
        ->call('create');

    $newSlug = 'new-slug';

    livewire(EditPage::class, ['record' => Str::slug($pageName)])
        ->fillForm([
            'name' => $pageName,
            'slug' => $newSlug,
            'slice_contents.record-1.data.main.header' => 'Bar',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    expect(
        Page::where('slug', $newSlug)->first()->sluggable
    )->toHaveCount(2);
});

it('allowed to get record using old slug', function () {
    $sliceId = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne()
        ->getKey();

    $pageName = 'Test Page';

    livewire(CreatePage::class)
        ->fillForm([
            'name' => $pageName,
            'slice_contents' => [
                [
                    'slice_id' => $sliceId,
                    'data' => ['name' => 'foo'],
                ],
            ],
        ])
        ->call('create');

    $newSlug = 'new-slug';

    livewire(EditPage::class, ['record' => Str::slug($pageName)])
        ->fillForm([
            'name' => $pageName,
            'slug' => $newSlug,
            'slice_contents.record-1.data.main.header' => 'Bar',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    livewire(EditPage::class, ['record' => Str::slug($pageName)])->assertOk();
    livewire(EditPage::class, ['record' => Str::slug($newSlug)])->assertOk();
});

it('Old slug can be assigned to new or existing record', function () {
    $pageOne = PageFactory::new(['name' => 'Page One'])
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

    $pageTwo = PageFactory::new(['name' => 'Page Two'])
        ->addSliceContent(
            SliceFactory::new()
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'test'])
                        ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
                ),
            ['data' => ['main' => ['header' => 'Foo']]]
        )
        ->createOne();

    // assertDatabaseCount(Page::class, 2);

    $newSlug = 'new-slug';

    // adding new slug to first page

    livewire(EditPage::class, ['record' => $pageOne->getRouteKey()])
        ->fillForm([
            'name' => $pageOne->name,
            'slug' => $newSlug,
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    //  assigning page one old slug to page two

    livewire(EditPage::class, ['record' => $pageTwo->getRouteKey()])
        ->fillForm([
            'name' => $pageTwo->name,
            'slug' => 'page-one',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();

    expect(
        Page::where('slug', $newSlug)->first()->sluggable
    )->toHaveCount(1);

    expect(
        Page::where('slug', 'page-one')->first()->sluggable
    )->toHaveCount(2);
});
