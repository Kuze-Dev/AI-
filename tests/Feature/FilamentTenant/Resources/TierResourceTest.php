<?php

declare(strict_types=1);

use App\Features\Customer\TierBase;
use App\FilamentTenant\Resources\TierResource;
use Domain\Tier\Database\Factories\TierFactory;
use Filament\Facades\Filament;

uses()->group('customer');

beforeEach(function () {
    testInTenantContext(TierBase::class);
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
