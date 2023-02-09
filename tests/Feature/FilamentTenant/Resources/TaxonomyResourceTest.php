<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\TaxonomyResource;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Filament\Facades\Filament;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can globally search', function () {
    $taxonomy = TaxonomyFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Description', 'type' => FieldType::TEXT])
        )
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($taxonomy->name);

    expect($results->getCategories()['taxonomies']->first()->url)
        ->toEqual(TaxonomyResource::getUrl('edit', [$taxonomy]));
});

it('can globally search using taxonomy term name', function () {
    $taxonomy = TaxonomyFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Description', 'type' => FieldType::TEXT])
        )
        ->has(TaxonomyTermFactory::new())
        ->createOne();

    $results = Filament::getGlobalSearchProvider()
        ->getResults($taxonomy->taxonomyTerms->first()->name);

    expect($results->getCategories()['taxonomies']->first()->url)
        ->toEqual(TaxonomyResource::getUrl('edit', [$taxonomy]));
});
