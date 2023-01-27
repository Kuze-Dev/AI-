<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\SliceResource;
use Filament\Facades\Filament;
use Domain\Page\Database\Factories\SliceFactory;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $slice = SliceFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($slice->name);

    expect($results->getCategories()['slices']->first()->url)
        ->toEqual(SliceResource::getUrl('edit', [$slice]));
});
