<?php

declare(strict_types=1);

use Carbon\Carbon;
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
        ->has(
            TaxonomyFactory::new()
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'Main'])
                        ->addSchemaField(['title' => 'Description', 'type' => FieldType::TEXT])
                )
        )
        ->createOne();

    CollectionEntryFactory::new()
        ->for($collection)
        ->has(
            TaxonomyTermFactory::new(['data' => ['main' => ['desciption' => 'Foo']]])
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

it('can filter collection entries by published at start', function () {
    $collection = CollectionFactory::new()
        ->publishDateBehaviour()
        ->withDummyBlueprint()
        ->createOne();

    CollectionEntryFactory::new()
        ->for($collection)
        ->count(2)
        ->sequence(
            ['published_at' => Carbon::now()->subWeeks(2)],
            ['published_at' => Carbon::now()->addWeeks(2)],
        )
        ->create([]);

    getJson("api/collections/{$collection->getRouteKey()}/entries?" . http_build_query(['filter' => ['published_at_start' => (string) Carbon::now()]]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 1)
                ->where('data.0.type', 'collectionEntries')
                ->etc();
        });
});

it('can filter collection entries by published at end', function () {
    $collection = CollectionFactory::new()
        ->publishDateBehaviour()
        ->withDummyBlueprint()
        ->createOne();

    CollectionEntryFactory::new()
        ->for($collection)
        ->count(2)
        ->sequence(
            ['published_at' => Carbon::now()->subWeeks(2)],
            ['published_at' => Carbon::now()->addWeeks(2)],
        )
        ->create();

    getJson("api/collections/{$collection->getRouteKey()}/entries?" . http_build_query(['filter' => ['published_at_end' => (string) Carbon::now()]]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 1)
                ->where('data.0.type', 'collectionEntries')
                ->etc();
        });
});

it('can filter collection entries by published at year month', function () {
    $collection = CollectionFactory::new()
        ->publishDateBehaviour()
        ->withDummyBlueprint()
        ->createOne();

    CollectionEntryFactory::new()
        ->for($collection)
        ->count(3)
        ->sequence(
            ['published_at' => Carbon::now()->subYear()],
            ['published_at' => Carbon::now()->subMonthNoOverflow()],
            ['published_at' => Carbon::now()],
        )
        ->create([]);

    $queryDate = Carbon::now();

    getJson("api/collections/{$collection->getRouteKey()}/entries?" . http_build_query(['filter' => ['published_at_year_month' => $queryDate->year]]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 2)
                ->where('data.0.type', 'collectionEntries')
                ->etc();
        });

    getJson("api/collections/{$collection->getRouteKey()}/entries?" . http_build_query(['filter' => ['published_at_year_month' => "{$queryDate->year},{$queryDate->month}"]]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 1)
                ->where('data.0.type', 'collectionEntries')
                ->etc();
        });
});

it('can filter collection entries by taxonomies', function () {
    $collection = CollectionFactory::new()
        ->publishDateBehaviour()
        ->withDummyBlueprint()
        ->has(
            TaxonomyFactory::new(['name' => 'Category'])
                ->withDummyBlueprint()
        )
        ->createOne();

    CollectionEntryFactory::new()
        ->for($collection)
        ->has(
            TaxonomyTermFactory::new(['name' => 'Laravel'])
                ->for($collection->taxonomies->first())
        )
        ->create();
    CollectionEntryFactory::new()
        ->for($collection)
        ->has(
            TaxonomyTermFactory::new(['name' => 'Livewire'])
                ->for($collection->taxonomies->first())
        )
        ->create();
    CollectionEntryFactory::new()
        ->for($collection)
        ->create();

    getJson("api/collections/{$collection->getRouteKey()}/entries?" . http_build_query(['filter' => ['taxonomies' => ['category' => 'laravel']]]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 1)
                ->where('data.0.type', 'collectionEntries')
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
        ->createOne();

    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->createOne(['data' => ['main' => ['header' => 'Foo']]]);

    getJson("api/collections/{$collection->getRouteKey()}/entries/{$collectionEntry->getRouteKey()}")
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($collectionEntry) {
            $json->where('data.type', 'collectionEntries')
                ->where('data.id', $collectionEntry->getRouteKey())
                ->where('data.attributes.title', $collectionEntry->title)
                ->where('data.attributes.route_url', $collectionEntry->qualified_route_url)
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
        ->has(
            TaxonomyFactory::new()
                ->for(
                    BlueprintFactory::new()
                        ->addSchemaSection(['title' => 'Main'])
                        ->addSchemaField(['title' => 'Description', 'type' => FieldType::TEXT])
                )
        )
        ->createOne();

    $collectionEntry = CollectionEntryFactory::new()
        ->for($collection)
        ->has(
            TaxonomyTermFactory::new(['data' => ['main' => ['desciption' => 'Foo']]])
                ->for($collection->taxonomies->first())
        )
        ->createOne([
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    $collectionEntry->metaData()->create([
        'title' => $collectionEntry->title,
        'description' => 'Foo description',
        'author' => 'Foo author',
        'keywords' => 'Foo keywords',
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
    'metaData',
]);
