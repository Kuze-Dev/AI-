<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Collection\Database\Factories\CollectionFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('can list collections', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $taxonomy = TaxonomyFactory::new()
        ->createOne();
    
    $collections = CollectionFactory::new()
        ->for($blueprint)
        ->count(10)
        ->create();

    foreach($collections as $collection) {
        $collection->taxonomies()->attach([ $taxonomy->getKey() ]);
    }

    getJson('api/collections')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 10)
                ->where('data.0.type','collections')
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });

});

it('can show a collection', function () {
    $blueprint = BlueprintFactory::new()
        ->withDummySchema()
        ->createOne();

    $taxonomy = TaxonomyFactory::new()
        ->createOne();

    $collection = CollectionFactory::new()
        ->for($blueprint)
        ->createOne();

    $collection->taxonomies()->attach([$taxonomy->getKey()]);
    
    getJson('api/collections/' . $collection->getRouteKey())
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($collection) {
            $json
                ->where('data.type', 'collections')
                ->where('data.id', Str::slug($collection->name))
                ->where('data.attributes.name', $collection->name)
                ->etc();
        });
});