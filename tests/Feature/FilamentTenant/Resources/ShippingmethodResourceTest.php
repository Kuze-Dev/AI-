<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\ShippingmethodResource;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $record = ShippingMethodFactory::new()->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($record->title);

    expect($results->getCategories()['shipping methods']->first()->url)
        ->toEqual(ShippingmethodResource::getUrl('edit', [$record]));
});
