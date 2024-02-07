<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\MenuResource\Pages\CreateMenu;
use Domain\Internationalization\Database\Factories\LocaleFactory;
use Domain\Menu\Database\Factories\MenuFactory;
use Domain\Menu\Enums\NodeType;
use Domain\Menu\Enums\Target;
use Domain\Menu\Models\Menu;
use Domain\Site\Database\Factories\SiteFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();

    LocaleFactory::createDefault();
});

it('can render page', function () {
    livewire(CreateMenu::class)
        ->assertFormExists()
        ->assertSuccessful();
});

it('can create menu', function () {
    livewire(CreateMenu::class)
        ->fillForm([
            'name' => 'Test Main Menu',
            'nodes' => [
                [
                    'label' => 'Test Home',
                    'target' => Target::BLANK->value,
                    'type' => NodeType::URL->value,
                    'url' => 'https://test-url-home.com',
                    'children' => [
                        [
                            'label' => 'Test Home',
                            'target' => Target::BLANK->value,
                            'type' => NodeType::URL->value,
                            'url' => 'https://test-url-home.com',
                        ],
                        [
                            'label' => 'Test About Child 2',
                            'target' => Target::BLANK->value,
                            'type' => NodeType::URL->value,
                            'url' => 'https://test-url-home.com',
                        ],
                    ],
                ],
            ],
        ]);
});

it('can create menu with sites', function () {

    tenancy()->tenant?->features()->activate(\App\Features\CMS\SitesManagement::class);

    $site = SiteFactory::new()->createOne();

    $menu = livewire(CreateMenu::class)
        ->fillForm([
            'name' => 'Test Main Menu',
            'sites' => [
                '1',
            ],

        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
         ->record;

    expect($menu->sites->pluck('id'))->toContain($site->id);

});

it('can create menu with same name on different sites', function () {

    tenancy()->tenant?->features()->activate(\App\Features\CMS\SitesManagement::class);

    $site = SiteFactory::new()->count(2)->create();

    $menu = MenuFactory::new()->create();

    $menu->sites()->sync(['1']);

    livewire(CreateMenu::class)
        ->fillForm([
            'name' => $menu->name,
            'sites' => [
                '2',
            ],

        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    assertDatabaseCount(Menu::class, 2);

});

it('cannot create menu with same name on same sites', function () {

    tenancy()->tenant?->features()->activate(\App\Features\CMS\SitesManagement::class);

    $site = SiteFactory::new()->create();

    $menu = MenuFactory::new()->create();

    $menu->sites()->sync(['1']);

    livewire(CreateMenu::class)
        ->fillForm([
            'name' => $menu->name,
            'sites' => [
                '1',
            ],

        ])
        ->call('create');

    assertDatabaseCount(Menu::class, 1);

});
