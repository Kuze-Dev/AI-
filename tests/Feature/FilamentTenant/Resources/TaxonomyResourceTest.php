<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxonomyResource;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Filament\Facades\Filament;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $taxonomy = TaxonomyFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($taxonomy->name);

    expect($results->getCategories()['taxonomies']->first()->url)
        ->toEqual(TaxonomyResource::getUrl('edit', [$taxonomy]));
});

it('can globally search using taxonomy term name', function () {
    $taxonomy = TaxonomyFactory::new()
        ->withDummyBlueprint()
        ->has(TaxonomyTermFactory::new())
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($taxonomy->taxonomyTerms->first()->name);

    expect($results->getCategories()['taxonomies']->first()->url)
        ->toEqual(TaxonomyResource::getUrl('edit', [$taxonomy]));
});
