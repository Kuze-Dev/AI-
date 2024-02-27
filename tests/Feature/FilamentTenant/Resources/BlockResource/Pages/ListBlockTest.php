<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\BlockResource\Blocks\ListBlocks;
use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\Database\Factories\PageFactory;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListBlocks::class)
        ->assertOk();
});

it('can list blocks', function () {
    $blocks = BlockFactory::new()
        ->withDummyBlueprint()
        ->count(5)
        ->create();

    livewire(ListBlocks::class)
        ->assertCanSeeTableRecords($blocks)
        ->assertOk();
});

it('can delete block', function () {
    $block = BlockFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    livewire(ListBlocks::class)
        ->callTableAction(DeleteAction::class, $block)
        ->assertOk();

    assertModelMissing($block);
});

it('can\'t delete block with existing content', function () {
    $block = BlockFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    PageFactory::new()
        ->addBlockContent($block)
        ->createOne();

    livewire(ListBlocks::class)
        ->callTableAction(DeleteAction::class, $block)
        ->assertNotified(trans(
            'Unable to :action :resource.',
            [
                'action' => 'delete',
                'resource' => 'block',
            ]
        ));
});
