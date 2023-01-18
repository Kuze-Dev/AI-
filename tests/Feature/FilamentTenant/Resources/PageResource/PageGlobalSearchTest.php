<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\SliceFactory;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('Page resource must be globaly searchable', function () {
    $data = PageFactory::new()
        ->create();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($data->name);

    expect(
        route('filament-tenant.resources.pages.edit', $data->getRouteKey())
    )->toEqual(
        $results->getCategories()['pages']->first()->url
    );
});

it('Slice resource must be globaly searchable', function () {
    $data = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($data->name);

    expect(
        route('filament-tenant.resources.slices.edit', $data->getRouteKey())
    )->toEqual(
        $results->getCategories()['slices']->first()->url
    );
});
