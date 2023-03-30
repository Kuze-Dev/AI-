<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\BlockResource;
use Filament\Facades\Filament;
use Domain\Page\Database\Factories\BlockFactory;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $block = BlockFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($block->name);

    expect($results->getCategories()['blocks']->first()->url)
        ->toEqual(BlockResource::getUrl('edit', [$block]));
});
