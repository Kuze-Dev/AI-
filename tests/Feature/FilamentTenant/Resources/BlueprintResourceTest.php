<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\BlueprintResource;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($blueprint->name);

    expect($results->getCategories()['blueprints']->first()->url)
        ->toEqual(BlueprintResource::getUrl('edit', [$blueprint]));
});
