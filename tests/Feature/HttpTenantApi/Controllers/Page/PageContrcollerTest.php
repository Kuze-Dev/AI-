<?php

declare(strict_types=1);

use Domain\Page\Database\Factories\PageFactory;

use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('return list', function () {
    PageFactory::new()
        ->withDummyBlueprint()
        ->count(10)
        ->create();

    getJson(route('tenant.api.pages.index'))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 10)
                ->where('data.0.type', 'pages')
                ->whereType('data.0.attributes.name', 'string')
                ->etc();
        });
});

it('show', function () {
    $page = PageFactory::new()
        ->withDummyBlueprint()
        ->createOne(['name' => 'My Page Title']);

    getJson(route('tenant.api.pages.show', $page))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($page) {
            $json
                ->where('data.type', 'pages')
                ->where('data.id', Str::slug($page->name))
                ->where('data.attributes.name', $page->name)
                ->etc();
        });
});

it('filter', function () {
    $pages = PageFactory::new()
        ->withDummyBlueprint()
        ->count(2)
        ->sequence(
            ['name' => 'page 1'],
            ['name' => 'page 2'],
        )
        ->create();

    foreach ($pages as $page) {
        getJson(route('tenant.api.pages.index', ['filter' => ['name' => $page->name]]))
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($page) {
                $json
                    ->count('data', 1)
                    ->where('data.0.type', 'pages')
                    ->where('data.0.id', $page->getRouteKey())
                    ->where('data.0.attributes.name', $page->name)
                    ->etc();
            });
        getJson('api/pages?'.http_build_query(['filter[slug]' => $page->slug]))
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
});
