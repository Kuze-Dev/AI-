<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\MenuResource\Pages\ListMenus;
use Domain\Menu\Database\Factories\MenuFactory;
use Domain\Menu\Database\Factories\NodeFactory;
use Filament\Pages\Actions\DeleteAction;

use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(ListMenus::class)->assertSuccessful();
});

it('can list Menus', function () {
    $menus = MenuFactory::new()
        ->has(NodeFactory::new())
        ->count(5)
        ->create();

    livewire(ListMenus::class)
        ->assertCanSeeTableRecords($menus);
});

it('can delete Menu', function () {
    $menu = MenuFactory::new()
        ->has(NodeFactory::new())
        ->createOne();
    $node = $menu->nodes->first();
    $nestedNode = NodeFactory::new([
        'menu_id' => $menu->id,
        'parent_id' => $node->id,
    ])->createOne();

    livewire(ListMenus::class)
        ->callTableAction(DeleteAction::class, $menu)
        ->assertOk();

    assertModelMissing($menu);
    assertModelMissing($node);
    assertModelMissing($nestedNode);
});
