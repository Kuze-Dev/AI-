<?php

declare(strict_types=1);

use App\Features\Customer\CustomerBase;
use App\FilamentTenant\Resources\CustomerResource;
use Domain\Customer\Database\Factories\CustomerFactory;
use Filament\Facades\Filament;

uses()->group('customer');

beforeEach(function () {
    testInTenantContext(features: CustomerBase::class);
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $customer = CustomerFactory::new()
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($customer->first_name);

    expect($results->getCategories()['customers']->first()->url)
        ->toEqual(CustomerResource::getUrl('edit', [$customer]));
});
