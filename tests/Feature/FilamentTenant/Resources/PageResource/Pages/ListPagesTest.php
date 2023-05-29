<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\ListPages;
use Carbon\Carbon;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\BlockFactory;
use Filament\Facades\Filament;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListPages::class)
        ->assertOk();
});

it('can list pages', function () {
    $pages = PageFactory::new()
        ->addBlockContent(BlockFactory::new()->withDummyBlueprint())
        ->count(5)
        ->create();

    livewire(ListPages::class)
        ->assertCanSeeTableRecords($pages)
        ->assertOk();
});

it('can delete page', function () {
    $page = PageFactory::new()
        ->addBlockContent(BlockFactory::new()->withDummyBlueprint())
        ->createOne();
    $blockContent = $page->blockContents->first();
    $metaData = $page->metaData;

    livewire(ListPages::class)
        ->callTableAction(DeleteAction::class, $page)
        ->assertOk();

    assertModelMissing($page);
    assertModelMissing($blockContent);
    assertModelMissing($metaData);
});
