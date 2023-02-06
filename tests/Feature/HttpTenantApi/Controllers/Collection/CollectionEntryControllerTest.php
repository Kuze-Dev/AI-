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
    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->has(TaxonomyFactory::new())
        ->createOne();

    CollectionEntryFactory::new()
        ->for($collection)
        ->has(
            TaxonomyTermFactory::new()
                ->for($collection->taxonomies->first())
        )
        ->count(10)
        ->create([
            'data' => ['main' => ['header' => 'Foo']],
        ]);

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
    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->has(TaxonomyFactory::new())
        ->createOne();

    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->has(
            TaxonomyTermFactory::new()
                ->for($collection->taxonomies->first())
        )
        ->createOne([
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    getJson("api/collections/{$collection->getRouteKey()}/entries/{$collectionEntry->getRouteKey()}")
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($collectionEntry) {
            $json->where('data.type', 'collectionEntries')
                ->where('data.id', $collectionEntry->getRouteKey())
                ->where('data.attributes.title', $collectionEntry->title)
                ->where('data.attributes.route_url',$collectionEntry->qualified_route_url)
                ->etc();
        });
});

it('can show collection entry with includes', function (string $include) {
    $collection = CollectionFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->has(TaxonomyFactory::new())
        ->createOne();

    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->has(
            TaxonomyTermFactory::new()
                ->for($collection->taxonomies->first())
        )
        ->createOne([
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    getJson("api/collections/{$collection->getRouteKey()}/entries/{$collectionEntry->getRouteKey()}?" . http_build_query(['include' => $include]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($collectionEntry, $include) {
            $json->where('data.type', 'collectionEntries')
                ->where('data.id', $collectionEntry->getRouteKey())
                ->where('data.attributes.title', $collectionEntry->title)
                ->has(
                    'included',
                    callback: fn (AssertableJson $json) => $json->where('type', $include)->etc()
                )
                ->etc();
        });
})->with([
    'taxonomyTerms',
    'slugHistories',
]);
