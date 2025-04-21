<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\PageResource\Pages\ListPages;
use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\Database\Factories\PageFactory;
use Filament\Pages\Actions\DeleteAction;
use Support\MetaData\Database\Factories\MetaDataFactory;
use Support\RouteUrl\Database\Factories\RouteUrlFactory;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
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
        ->has(RouteUrlFactory::new())
        ->has(MetaDataFactory::new())
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
