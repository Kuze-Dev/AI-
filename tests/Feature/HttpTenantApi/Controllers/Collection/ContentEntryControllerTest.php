<?php

declare(strict_types=1);

use Domain\Blueprint\Database\Factories\BlueprintFactory;
use Domain\Blueprint\Enums\FieldType;
use Domain\Content\Database\Factories\ContentEntryFactory;
use Domain\Content\Database\Factories\ContentFactory;
use Domain\Site\Database\Factories\SiteFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyTermFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('can list content entries', function () {
    $content = ContentFactory::new()
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

    ContentEntryFactory::new()
        ->for($content)
        ->has(
            TaxonomyTermFactory::new(['data' => ['main' => ['desciption' => 'Foo']]])
                ->for($content->taxonomies->first())
        )
        ->count(10)
        ->create([
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    getJson("api/contents/{$content->getRouteKey()}/entries")
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 10)
                ->where('data.0.type', 'contentEntries')
                ->whereType('data.0.attributes.title', 'string')
                ->etc();
        });
});

it('can filter content entries by published at start', function () {
    $content = ContentFactory::new()
        ->publishDateBehaviour()
        ->withDummyBlueprint()
        ->createOne();

    ContentEntryFactory::new()
        ->for($content)
        ->count(2)
        ->sequence(
            ['published_at' => now()->subWeeks(2)],
            ['published_at' => now()->addWeeks(2)],
        )
        ->create([]);

    getJson("api/contents/{$content->getRouteKey()}/entries?".http_build_query(['filter' => ['published_at_start' => (string) now()]]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 1)
                ->where('data.0.type', 'contentEntries')
                ->etc();
        });
});

it('can filter content entries by published at end', function () {
    $content = ContentFactory::new()
        ->publishDateBehaviour()
        ->withDummyBlueprint()
        ->createOne();

    ContentEntryFactory::new()
        ->for($content)
        ->count(2)
        ->sequence(
            ['published_at' => now()->subWeeks(2)],
            ['published_at' => now()->addWeeks(2)],
        )
        ->create();

    getJson("api/contents/{$content->getRouteKey()}/entries?".http_build_query(['filter' => ['published_at_end' => (string) now()]]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 1)
                ->where('data.0.type', 'contentEntries')
                ->etc();
        });
});

it('can filter content entries by published at year month', function () {
    $content = ContentFactory::new()
        ->publishDateBehaviour()
        ->withDummyBlueprint()
        ->createOne();

    ContentEntryFactory::new()
        ->for($content)
        ->count(2)
        ->sequence(
            ['published_at' => now()->subYear()],
            ['published_at' => now()],
        )
        ->create([]);

    $queryDate = now();

    getJson("api/contents/{$content->getRouteKey()}/entries?".http_build_query(['filter' => ['published_at_year_month' => $queryDate->year]]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 1)
                ->where('data.0.type', 'contentEntries')
                ->etc();
        });

    getJson("api/contents/{$content->getRouteKey()}/entries?".http_build_query(['filter' => ['published_at_year_month' => "{$queryDate->year},{$queryDate->month}"]]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 1)
                ->where('data.0.type', 'contentEntries')
                ->etc();
        });
});

it('can filter content entries by taxonomies', function () {
    $content = ContentFactory::new()
        ->publishDateBehaviour()
        ->withDummyBlueprint()
        ->has(
            TaxonomyFactory::new(['name' => 'Category'])
                ->withDummyBlueprint()
        )
        ->createOne();

    ContentEntryFactory::new()
        ->for($content)
        ->has(
            TaxonomyTermFactory::new(['name' => 'Laravel'])
                ->for($content->taxonomies->first())
        )
        ->create();
    ContentEntryFactory::new()
        ->for($content)
        ->has(
            TaxonomyTermFactory::new(['name' => 'Livewire'])
                ->for($content->taxonomies->first())
        )
        ->create();
    ContentEntryFactory::new()
        ->for($content)
        ->create();

    getJson("api/contents/{$content->getRouteKey()}/entries?".http_build_query(['filter' => ['taxonomies' => ['category' => 'laravel']]]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 1)
                ->where('data.0.type', 'contentEntries')
                ->etc();
        });
});

it('can show content entry', function () {
    $content = ContentFactory::new()
        ->for(
            BlueprintFactory::new()
                ->addSchemaSection(['title' => 'Main'])
                ->addSchemaField(['title' => 'Header', 'type' => FieldType::TEXT])
        )
        ->createOne();

    $contentEntry = ContentEntryFactory::new()
        ->for($content)
        ->createOne(['data' => ['main' => ['header' => 'Foo']]]);

    getJson("api/contents/{$content->getRouteKey()}/entries/{$contentEntry->getRouteKey()}")
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($contentEntry) {
            $json->where('data.type', 'contentEntries')
                ->where('data.id', $contentEntry->getRouteKey())
                ->where('data.attributes.title', $contentEntry->title)
                ->where('data.attributes.route_url', $contentEntry->activeRouteUrl->url)
                ->etc();
        });
});

it('can show content entry with includes', function (string $include) {
    $content = ContentFactory::new()
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

    $contentEntry = ContentEntryFactory::new()
        ->for($content)
        ->has(
            TaxonomyTermFactory::new(['data' => ['main' => ['desciption' => 'Foo']]])
                ->for($content->taxonomies->first())
        )
        ->createOne([
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    $contentEntry->metaData()->create([
        'title' => $contentEntry->title,
        'description' => 'Foo description',
        'author' => 'Foo author',
        'keywords' => 'Foo keywords',
    ]);

    // dd($contentEntry->routeUrls);

    getJson("api/contents/{$content->getRouteKey()}/entries/{$contentEntry->getRouteKey()}?".http_build_query(['include' => $include]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($contentEntry, $include) {
            $json->where('data.type', 'contentEntries')
                ->where('data.id', $contentEntry->getRouteKey())
                ->where('data.attributes.title', $contentEntry->title)
                ->has(
                    'included',
                    callback: fn (AssertableJson $json) => $json->where('type', $include)->etc()
                )
                ->etc();
        });
})->with([
    'taxonomyTerms',
    'routeUrls',
    'metaData',
]);

it('can list content entries of specific site', function () {
    $content = ContentFactory::new()
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

    $site = SiteFactory::new()
        ->createOne();

    ContentEntryFactory::new()
        ->for($content)
        ->has(
            TaxonomyTermFactory::new(['data' => ['main' => ['desciption' => 'Foo']]])
                ->for($content->taxonomies->first())
        )
        ->count(2)
        ->create([
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    ContentEntryFactory::new()
        ->for($content)
        ->has(
            TaxonomyTermFactory::new(['data' => ['main' => ['desciption' => 'Foo']]])
                ->for($content->taxonomies->first())
        )
        ->hasAttached($site)
        ->count(1)
        ->create([
            'data' => ['main' => ['header' => 'Foo']],
        ]);

    getJson("api/contents/{$content->getRouteKey()}/entries?filter[sites.id]={$site->id}")
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->count('data', 1)
                ->where('data.0.type', 'contentEntries')
                ->whereType('data.0.attributes.title', 'string')
                ->etc();
        });
});
