<?php

declare(strict_types=1);

use Domain\Content\Database\Factories\ContentFactory;
use Domain\Taxonomy\Database\Factories\TaxonomyFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('can list contents', function () {
    ContentFactory::new()
        ->withDummyBlueprint()
        ->count(10)
        ->create();

    getJson('api/contents')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 10)
                ->where('data.0.type', 'contents')
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });
});

it('can show a content', function () {
    $content = ContentFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    getJson('api/contents/' . $content->getRouteKey())
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($content) {
            $json
                ->where('data.type', 'contents')
                ->where('data.id', $content->getRouteKey())
                ->where('data.attributes.name', $content->name)
                ->etc();
        });
});

it('can show a content with includes', function (string $include) {
    $content = ContentFactory::new()
        ->withDummyBlueprint()
        ->has(
            TaxonomyFactory::new()
                ->withDummyBlueprint()
        )
        ->createOne();

    getJson("api/contents/{$content->getRouteKey()}?" . http_build_query(['include' => $include]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($content, $include) {
            $json
                ->where('data.type', 'contents')
                ->where('data.id', $content->getRouteKey())
                ->where('data.attributes.name', $content->name)
                ->has(
                    'included',
                    callback: fn (AssertableJson $json) => $json->where('type', $include)->etc()
                )
                ->etc();
        });
})->with([
    'taxonomies',
]);
