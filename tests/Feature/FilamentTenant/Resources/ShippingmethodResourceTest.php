<?php

declare(strict_types=1);

use App\Features\ECommerce\ECommerceBase;
use App\Features\ECommerce\ShippingStorePickup;
use App\FilamentTenant\Resources\ShippingmethodResource;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();

    tenancy()->tenant->features()->activate(ECommerceBase::class);
    tenancy()->tenant->features()->activate(ShippingStorePickup::class);
});

it('can globally search', function () {
    $record = ShippingMethodFactory::new()->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($record->title);

    expect($results->getCategories()['shipping methods']->first()->url)
        ->toEqual(ShippingmethodResource::getUrl('edit', [$record]));
});
