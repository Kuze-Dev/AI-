<?php

declare(strict_types=1);

use Domain\Menu\Database\Factories\MenuFactory;

use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('show', function () {
    $menu = MenuFactory::new()
        ->createOne([
            'name' => 'Test Main Menu'
        ]);
    getJson('api/menus/' . $menu->getRouteKey())
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($menu) {
            $json
                ->where('data.id', Str::slug($menu->name))
                ->where('data.attributes.name', $menu->name)
                ->etc();
        });
});
