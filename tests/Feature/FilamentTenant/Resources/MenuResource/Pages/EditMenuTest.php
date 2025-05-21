<?php

declare(strict_types=1);

use App\Features\CMS\SitesManagement;
use App\FilamentTenant\Resources\MenuResource\Pages\EditMenu;
use Domain\Internationalization\Database\Factories\LocaleFactory;
use Domain\Menu\Database\Factories\MenuFactory;
use Domain\Menu\Database\Factories\NodeFactory;
use Domain\Menu\Enums\NodeType;
use Domain\Menu\Enums\Target;
use Domain\Menu\Models\Menu;
use Domain\Site\Database\Factories\SiteFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext(SitesManagement::class);
    loginAsSuperAdmin();

    LocaleFactory::createDefault();
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

    $site = SiteFactory::new()
        ->createOne();

    livewire(EditMenu::class, ['record' => $menu->getRouteKey()])
        ->fillForm(
            [
                'name' => 'Test Edit Menu',
                'sites' => [$site->id],
                'nodes' => [
                    [
                        'label' => 'Test Edit Node',
                        'target' => Target::BLANK->value,
                        'type' => NodeType::URL->value,
                        'url' => 'https://test-edit-node.com',
                        'children' => [
                            [
                                'label' => 'Test Edit Child',
                                'target' => Target::BLANK->value,
                                'type' => NodeType::URL->value,
                                'url' => 'https://test-edit-node.com',
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
