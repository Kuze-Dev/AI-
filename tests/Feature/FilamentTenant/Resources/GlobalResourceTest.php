<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\GlobalsResource;
use Domain\Globals\Database\Factories\GlobalsFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $record = GlobalsFactory::new()->withDummyBlueprint()->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($record->name);

    expect($results->getCategories()['globals']->first()->url)
        ->toEqual(GlobalsResource::getUrl('edit', [$record]));
});
