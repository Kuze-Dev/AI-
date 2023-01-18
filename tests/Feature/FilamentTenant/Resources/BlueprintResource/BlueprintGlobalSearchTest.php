<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('blueprint resource must be globaly searchable', function () {
    $data = BlueprintFactory::new()
        ->withDummySchema()
        ->create();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($data->name);

    expect(
        route('filament-tenant.resources.blueprints.edit', $data->getRouteKey())
    )->toEqual(
        $results->getCategories()['blueprints']->first()->url
    );
});
