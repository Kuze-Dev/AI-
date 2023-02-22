<?php

declare(strict_types=1);

use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Database\Factories\SliceFactory;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('can list pages', function () {
    PageFactory::new()
        ->addSliceContent(SliceFactory::new()->withDummyBlueprint())
        ->count(10)
        ->create();

    getJson('api/pages')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 10)
                ->where('data.0.type', 'pages')
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });
});

it('can filter pages', function ($attribute) {
    $pages = PageFactory::new()
        ->addSliceContent(SliceFactory::new()->withDummyBlueprint())
        ->count(2)
        ->sequence(
            ['name' => 'Foo'],
            ['name' => 'Bar'],
        )
        ->create();

    foreach ($pages as $page) {
        getJson('api/pages?' . http_build_query(['filter' => [$attribute => $page->$attribute]]))
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($page) {
                $json
                    ->count('data', 1)
                    ->where('data.0.type', 'pages')
                    ->where('data.0.id', $page->getRouteKey())
                    ->where('data.0.attributes.name', $page->name)
                    ->etc();
            });
    }
})->with(['name', 'slug']);

it('can show a page with includes', function (string $include) {
    $page = PageFactory::new()
        ->addSliceContent(SliceFactory::new()->withDummyBlueprint())
        ->createOne();

    $page->metaData()->create([
        'title' => $page->name,
        'description' => 'Foo description',
        'author' => 'Foo author',
        'keywords' => 'Foo keywords',
    ]);

    getJson("api/pages/{$page->getRouteKey()}?" . http_build_query(['include' => $include]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($page, $include) {
            $json
                ->where('data.type', 'pages')
                ->where('data.id', Str::slug($page->name))
                ->where('data.attributes.name', $page->name)
                ->where('data.attributes.route_url', $page->qualified_route_url)
                ->has(
                    'included',
                    callback: fn (AssertableJson $json) => $json->where('type', $include)->etc()
                )
                ->etc();
        });
})->with([
    'sliceContents',
    'slugHistories',
    'metaData',
]);
