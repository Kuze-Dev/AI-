<?php

declare(strict_types=1);

use Domain\Menu\Database\Factories\MenuFactory;
use Domain\Menu\Database\Factories\NodeFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('show', function () {
    $menu = MenuFactory::new()
        ->createOne([
            'name' => 'Test Main Menu',
        ]);

    $nodes = NodeFactory::new()
        ->for($menu)
        ->count(2)
        ->create();

    getJson('api/menus/' . $menu->getRouteKey())
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($menu) {
            $json
                ->where('data.type', 'menus')
                ->where('data.id', Str::slug($menu->name))
                ->where('data.attributes.name', $menu->name)
                ->etc();
        });
});
