<?php

declare(strict_types=1);

use Illuminate\Testing\Fluent\AssertableJson;
use Domain\Menu\Database\Factories\MenuFactory;
use Domain\Menu\Database\Factories\NodeFactory;

use Domain\Site\Database\Factories\SiteFactory;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('can list menus', function () {
    MenuFactory::new()
        ->has(
            NodeFactory::new()
                ->count(3)
        )
        ->count(10)
        ->create();

    getJson('api/menus')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 10)
                ->where('data.0.type', 'menus')
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });
});

it('can filter pages', function ($attribute) {
    $menus = MenuFactory::new()
        ->has(
            NodeFactory::new()
                ->count(3)
        )
        ->count(2)
        ->sequence(
            ['name' => 'Foo', 'locale' => 'de'],
            ['name' => 'Bar', 'locale' => 'fr'],
        )
        ->create();

    foreach ($menus as $menu) {
        getJson('api/menus?' . http_build_query(['filter' => [$attribute => $menu->$attribute]]))
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($menu) {
                $json
                    ->count('data', 1)
                    ->where('data.0.type', 'menus')
                    ->where('data.0.id', $menu->getRouteKey())
                    ->where('data.0.attributes.name', $menu->name)
                    ->etc();
            });
    }
})->with(['name', 'slug', 'locale']);

it('can show menu', function () {
    $menu = MenuFactory::new()
        ->has(
            NodeFactory::new()
                ->count(3)
        )
        ->createOne(['name' => 'Test Main Menu']);

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

it('can show menu with includes', function (string $include) {
    $menu = MenuFactory::new()
        ->has(
            NodeFactory::new()
                ->count(3)
        )
        ->createOne(['name' => 'Test Main Menu']);

    getJson("api/menus/{$menu->getRouteKey()}?" . http_build_query(['include' => $include]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($menu, $include) {
            $json
                ->where('data.type', 'menus')
                ->where('data.id', Str::slug($menu->name))
                ->where('data.attributes.name', $menu->name)
                ->has(
                    'included',
                    callback: fn (AssertableJson $json) => $json->where('type', $include === 'parentNodes' ? 'nodes' : $include)->etc()
                )
                ->etc();
        });
})->with([
    'nodes',
    'parentNodes',
]);

it('can list menus of specific site', function () {
    $site = SiteFactory::new()->createOne();
    MenuFactory::new()
        ->has(
            NodeFactory::new()
                ->count(3)
        )
        ->hasAttached($site)
        ->count(1)
        ->create();
    MenuFactory::new()
        ->has(
            NodeFactory::new()
                ->count(3)
        )
        ->count(2)
        ->create();

    getJson("api/menus?filter[sites.id]={$site->id}")
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 1)
                ->where('data.0.type', 'menus')
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });
});
