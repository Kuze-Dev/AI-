<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\EditPage;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Models\Page;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsAdmin();
});

it('can render page', function () {
    $page = PageFactory::new()
        ->createOne();

    livewire(EditPage::class, ['record' => $page->getKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => $page->name,
        ]);
});

it('can edit page', function () {
    $page = PageFactory::new()->createOne();

    livewire(EditPage::class, ['record' => $page->getKey()])
        ->fillForm([
            'name' => 'Test',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Page::class, ['name' => 'Test']);
});

it('can not update page with same name', function () {
    PageFactory::new()->createOne([
        'name' => 'page 1',
    ]);

    $page = PageFactory::new()->createOne();

    assertDatabaseCount(Page::class, 2);

    livewire(EditPage::class, ['record' => $page->getKey()])
        ->fillForm([
            'name' => 'page 1',
            'blueprint_id' => $page->blueprint->getKey(),
        ])
        ->call('save')
        ->assertHasFormErrors(['name' => 'unique']);

    assertDatabaseCount(Page::class, 2);
});
