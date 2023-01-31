<?php

declare(strict_types=1);

use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('can list collections', function () {
    CollectionFactory::new()
        ->withDummyBlueprint()
        ->count(10)
        ->create();

    getJson('api/collections')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 10)
                ->where('data.0.type', 'collections')
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });
});

it('can list collections with taxonomies', function () {
    CollectionFactory::new()
        ->withDummyBlueprint()
        ->has(TaxonomyFactory::new())
        ->count(10)
        ->create();

    getJson('api/collections?include=taxonomies')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 10)
                ->where('data.0.type', 'collections')
                ->whereType('data.0.attributes.name', 'string')
                ->where('data.0.relationships.taxonomies.data.0.type', 'taxonomies')
                ->etc();
        });
});

it('can show a collection', function () {
    $collection = CollectionFactory::new()
        ->withDummyBlueprint()
        ->has(TaxonomyFactory::new())
        ->createOne();

    getJson('api/collections/' . $collection->getRouteKey())
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($collection) {
            $json
                ->where('data.type', 'collections')
                ->where('data.id', $collection->getRouteKey())
                ->where('data.attributes.name', $collection->name)
                ->etc();
        });
});

it('can show a collection with includes', function (string $include) {
    $collection = CollectionFactory::new()
        ->withDummyBlueprint()
        ->has(TaxonomyFactory::new())
        ->createOne();

    getJson("api/collections/{$collection->getRouteKey()}?" . http_build_query(['include' => $include]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($collection, $include) {
            $json
                ->where('data.type', 'collections')
                ->where('data.id', $collection->getRouteKey())
                ->where('data.attributes.name', $collection->name)
                ->has(
                    'included',
                    callback: fn (AssertableJson $json) => $json->where('type', $include)->etc()
                )
                ->etc();
        });
})->with([
    'taxonomies',
    'slugHistories',
]);
