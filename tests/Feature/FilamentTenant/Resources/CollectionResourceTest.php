<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Domain\Collection\Database\Factories\CollectionFactory;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $collection = CollectionFactory::new()
        ->withDummyBlueprint()
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'private',
            'past_publish_date_behavior' => 'unlisted',
            'is_sortable' => true,
        ]);

    $result = Filament::getGlobalSearchProvider()
        ->getResults($collection->name)
        ->getCategories()['collections']
        ->first();

    expect($result->url)->toEqual(
        route('filament-tenant.resources.collections.edit', $collection->getRouteKey())
    );
});
