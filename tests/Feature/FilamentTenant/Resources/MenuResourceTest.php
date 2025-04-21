<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\MenuResource;
use Domain\Menu\Database\Factories\MenuFactory;
use Domain\Menu\Database\Factories\NodeFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $menu = MenuFactory::new()
        ->has(NodeFactory::new(), 'nodes')
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($menu->name);

    expect($results->getCategories()['menus']->first()->url)
        ->toEqual(MenuResource::getUrl('edit', [$menu]));
});

it('can globally search using node url', function () {
    $menu = MenuFactory::new()
        ->has(NodeFactory::new(), 'nodes')
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($menu->nodes->first()->url);

    expect($results->getCategories()['menus']->first()->url)
        ->toEqual(MenuResource::getUrl('edit', [$menu]));
});

it('can globally search using node label', function () {
    $menu = MenuFactory::new()
        ->has(NodeFactory::new(), 'nodes')
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($menu->nodes->first()->label);

    expect($results->getCategories()['menus']->first()->url)
        ->toEqual(MenuResource::getUrl('edit', [$menu]));
});
