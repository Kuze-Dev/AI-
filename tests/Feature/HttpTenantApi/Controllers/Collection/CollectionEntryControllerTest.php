<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Collection\Database\Factories\CollectionEntryFactory;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('can list collection entries', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $taxonomyTerms = TaxonomyTermFactory::new()
        ->for($taxonomy)
        ->count(5)
        ->create();

    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne();
    
    $collection->taxonomies()->attach([ $taxonomy->getKey() ]);

    $collectionEntries = CollectionEntryFactory::new()
        ->for($collection)
        ->count(10)
        ->create([
            'data' => ['main' => ['header' => 'Foo']]
        ]);
    
    foreach($collectionEntries as $entry) {
        $entry->taxonomyTerms()->attach($taxonomyTerms->pluck('id'));
    }

    getJson("api/collections/{$collection->getRouteKey()}/entries")
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 10)
                ->where('data.0.type', 'collectionEntries')
                ->whereType('data.0.attributes.title', 'string')
                ->etc();
        });

});

it('can show collection entry', function () {
    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $taxonomyTerms = TaxonomyTermFactory::new()
        ->for($taxonomy)
        ->count(5)
        ->createOne();

    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne();
    
    $collection->taxonomies()->attach([ $taxonomy->getKey() ]);

    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->createOne([
            'data' => ['main' => ['header' => 'Foo']]
        ]);
    
    $collectionEntry->taxonomyTerms()->attach($taxonomyTerms->pluck('id'));

    getJson("api/collections/{$collection->getRouteKey()}/entries/{$collectionEntry->getRouteKey()}")
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->where('data.type', 'collectionEntries')
                ->whereType('data.attributes.title', 'string')
                ->etc();
        });

});