<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TierResource;
use Domain\Tier\Database\Factories\TierFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $tier = TierFactory::new()
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($tier->name);

    expect($results->getCategories()['tiers']->first()->url)
        ->toEqual(TierResource::getUrl('edit', [$tier]));
});
