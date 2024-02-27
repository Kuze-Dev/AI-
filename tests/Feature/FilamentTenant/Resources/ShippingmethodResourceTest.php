<?php

declare(strict_types=1);

use App\Features\ECommerce\ECommerceBase;
use App\Features\Shopconfiguration\Shipping\ShippingStorePickup;
use App\FilamentTenant\Resources\ShippingMethodResource;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext(features: [
        ECommerceBase::class,
        ShippingStorePickup::class,
    ]);
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $record = ShippingMethodFactory::new()->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($record->title);

    expect($results->getCategories()['shipping methods']->first()->url)
        ->toEqual(ShippingMethodResource::getUrl('edit', [$record]));
});
