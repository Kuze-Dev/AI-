<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\BlockResource;
use Domain\Page\Database\Factories\BlockFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
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
