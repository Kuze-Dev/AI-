<?php

declare(strict_types=1);

use Domain\Menu\Database\Factories\MenuFactory;
use Domain\Menu\Database\Factories\NodeFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('menu resource must be globaly searchable', function () {
    $data = MenuFactory::new()
        ->has(NodeFactory::new(), 'nodes')
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($data->name);

    expect(
        route('filament-tenant.resources.menus.edit', $data->getRouteKey())
    )->toEqual(
        $results->getCategories()['menus']->first()->url
    );
});

it('menu node url must be globaly searchable', function () {
    $data = MenuFactory::new()
        ->has(NodeFactory::new(), 'nodes')
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($data->nodes->first()->url);

    expect(
        route('filament-tenant.resources.menus.edit', $data->getRouteKey())
    )->toEqual(
        $results->getCategories()['menus']->first()->url
    );
});

it('menu node label must be globaly searchable', function () {
    $data = MenuFactory::new()
        ->has(NodeFactory::new(), 'nodes')
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($data->nodes->first()->label);

    expect(
        route('filament-tenant.resources.menus.edit', $data->getRouteKey())
    )->toEqual(
        $results->getCategories()['menus']->first()->url
    );
});
