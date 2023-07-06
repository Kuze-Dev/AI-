<?php

declare(strict_types=1);

use Domain\Content\Database\Factories\ContentEntryFactory;
use Domain\Content\Database\Factories\ContentFactory;
use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\Database\Factories\PageFactory;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('can search', function () {
    PageFactory::new()
        ->addBlockContent(BlockFactory::new()->withDummyBlueprint())
        ->published()
        ->count(5)
        ->create();

    PageFactory::new()
        ->addBlockContent(BlockFactory::new()->withDummyBlueprint())
        ->published()
        ->count(5)
        ->sequence(...iterator_to_array((function () {
            foreach (range(1, 5) as $count) {
                yield ['name' => 'foo ' . $count];
            }
        })()))
        ->create();

    ContentEntryFactory::new()
        ->for(
            ContentFactory::new()
                ->withDummyBlueprint()
                ->createOne()
        )
        ->count(5)
        ->create();

    ContentEntryFactory::new()
        ->for(
            ContentFactory::new()
                ->withDummyBlueprint()
                ->createOne()
        )
        ->count(5)
        ->sequence(...iterator_to_array((function () {
            foreach (range(1, 5) as $count) {
                yield ['title' => 'foo ' . $count];
            }
        })()))
        ->create();

    getJson('api/search?' . http_build_query(['query' => 'foo']))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('data', 10)
                ->etc();
        });
});

it('can search pages', function (string $model) {
    PageFactory::new()
        ->addBlockContent(BlockFactory::new()->withDummyBlueprint())
        ->published()
        ->count(5)
        ->sequence(...iterator_to_array((function () {
            foreach (range(1, 5) as $count) {
                yield ['name' => 'foo ' . $count];
            }
        })()))
        ->create();
    ContentEntryFactory::new()
        ->for(
            ContentFactory::new()
                ->withDummyBlueprint()
                ->createOne()
        )
        ->count(5)
        ->sequence(...iterator_to_array((function () {
            foreach (range(1, 5) as $count) {
                yield ['title' => 'foo ' . $count];
            }
        })()))
        ->create();

    getJson('api/search?' . http_build_query(['query' => 'foo', 'filter' => ['models' => $model]]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($model) {
            $json->has('data', 5)
                ->etc();

            expect($json->toArray()['data'])->each->toMatchArray(['type' => Str::plural($model)]);
        });
})->with(['page', 'contentEntry']);

it('can search content entries from specific content', function () {
    ContentEntryFactory::new()
        ->for(
            $content = ContentFactory::new()
                ->withDummyBlueprint()
                ->createOne()
        )
        ->count(5)
        ->sequence(...iterator_to_array((function () {
            foreach (range(1, 5) as $count) {
                yield ['title' => 'foo ' . $count];
            }
        })()))
        ->create();
    ContentEntryFactory::new()
        ->for(
            ContentFactory::new()
                ->withDummyBlueprint()
                ->createOne()
        )
        ->count(5)
        ->sequence(...iterator_to_array((function () {
            foreach (range(1, 5) as $count) {
                yield ['title' => 'foo ' . $count];
            }
        })()))
        ->create();

    getJson('api/search?' . http_build_query(['query' => 'foo', 'filter' => ['models' => 'contentEntry', 'content_ids' => $content->getKey()]]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json->has('data', 5)
                ->etc();

            expect($json->toArray()['data'])->each->toMatchArray([
                'type' => 'contentEntries',
            ]);
        });
});
