<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\MenuResource\Pages\EditMenu;
use Domain\Menu\Database\Factories\MenuFactory;
use Domain\Menu\Database\Factories\NodeFactory;
use Domain\Menu\Models\Menu;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    $menu = MenuFactory::new()
        ->has(NodeFactory::new(), 'nodes')
        ->createOne();
    livewire(EditMenu::class, ['record' => $menu->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => $menu->name,
        ]);
});

it('can edit menu', function () {
    $menu = MenuFactory::new()
        ->has(NodeFactory::new(), 'nodes')
        ->createOne();

    livewire(EditMenu::class, ['record' => $menu->getRouteKey()])
        ->fillForm(
            [
                'name' => 'Test Edit Menu',
                'nodes' => [
                    [
                        'label' => 'Test Edit Node',
                        'url' => 'https://test-edit-node.com',
                        'target' => '_blank',
                        'childs' => [
                            [
                                'label' => 'Test Edit Child',
                                'url' => 'https://test-edit-child.com',
                                'target' => '_blank',
                            ],
                        ],
                    ],
                ],
            ]
        )
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Menu::class, ['name' => 'Test Edit Menu']);
});
