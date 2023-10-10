<?php

declare(strict_types=1);

use App\Features\Service\ServiceBase;
use App\FilamentTenant\Resources\ServiceResource;
use Domain\Service\Databases\Factories\ServiceFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();

    tenancy()->tenant->features()->activate(ServiceBase::class);
});

it('can globally search', function () {

    $service = ServiceFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($service->name);

    expect($results->getCategories()['services']->first()->url)
        ->toEqual(ServiceResource::getUrl('edit', [$service]));
});
