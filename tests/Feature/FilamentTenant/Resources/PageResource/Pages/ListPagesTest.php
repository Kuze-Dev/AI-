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

it('can filter pages by published at range', function () {
    $pages = PageFactory::new()
        ->addBlockContent(BlockFactory::new()->withDummyBlueprint())
        ->count(5)
        ->sequence(
            ['published_at' => Carbon::now()->subWeeks(3)],
            ['published_at' => Carbon::now()->subWeeks(2)],
            ['published_at' => Carbon::now()],
            ['published_at' => Carbon::now()->addWeeks(2)],
            ['published_at' => Carbon::now()->addWeeks(3)],
        )
        ->create();

    livewire(ListPages::class)
        ->assertCanSeeTableRecords($pages)
        ->assertCountTableRecords(6)
        ->filterTable('published_at_range', [
            'published_at_from' => Carbon::now()->subDay(),
            'published_at_to' => null,
        ])
        ->assertCountTableRecords(4)
        ->filterTable('published_at_range', [
            'published_at_from' => null,
            'published_at_to' => Carbon::now()->addDay(),
        ])
        ->assertCountTableRecords(4)
        ->filterTable('published_at_range', [
            'published_at_from' => Carbon::now()->subDay(),
            'published_at_to' => Carbon::now()->addDay(),
        ])
        ->assertCountTableRecords(2)
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
