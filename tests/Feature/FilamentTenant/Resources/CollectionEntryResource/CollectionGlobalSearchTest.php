<?php

declare(strict_types=1);

use Filament\Facades\Filament;
use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Domain\Collection\Database\Factories\CollectionEntryFactory;
use Carbon\Carbon;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('collection Entries resource must be globaly searchable', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $taxonomyTermsInitial = TaxonomyTermFactory::new()
        ->for($taxonomy)
        ->count(2)
        ->create();

    $taxonomyTermsForUpdate = TaxonomyTermFactory::new()
        ->for($taxonomy)
        ->count(2)
        ->create();

    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne([
            'name' => 'Test Collection',
            'future_publish_date_behavior' => 'public',
            'past_publish_date_behavior' => 'unlisted',
        ]);
    $collection->taxonomies()->attach([$taxonomy->getKey()]);

    $dateTime = Carbon::now();

    $originalData = [
        'title' => 'Test',
        'slug' => 'test',
        'published_at' => $dateTime,
        'data' => json_encode(['main' => ['header' => 'Foo']]),
    ];

    $collectionEntry = CollectionEntryFactory::new()
        ->for(
            $collection
        )
        ->createOne($originalData);

    $collectionEntry->taxonomyTerms()->attach($taxonomyTermsInitial->pluck('id'));

    $results = Filament::getGlobalSearchProvider()
        ->getResults($collectionEntry->title);

    expect(
        route('filament-tenant.resources.collections.entries.edit', [
            'ownerRecord' => $collection,
            'record' => $collectionEntry,
        ])
    )->toEqual(
        $results->getCategories()['collection entries']->first()->url
    );
});
