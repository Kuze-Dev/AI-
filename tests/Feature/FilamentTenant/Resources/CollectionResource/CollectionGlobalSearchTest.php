<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('collection resource must be globaly searchable', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'private',
            'past_publish_date_behavior' => 'unlisted',
            'is_sortable' => true,
        ]);

    $collection->taxonomies()
        ->attach([
            $taxonomy->getKey(),
        ]);

    $results = Filament::getGlobalSearchProvider()
        ->getResults($collection->name);

    expect(
        route('filament-tenant.resources.collections.edit', $collection->getRouteKey())
    )->toEqual(
        $results->getCategories()['collections']->first()->url
    );
});
