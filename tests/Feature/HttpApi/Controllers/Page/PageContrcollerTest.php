<?php

declare(strict_types=1);

use Domain\Page\Database\Factories\PageFactory;

use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
    loginAsUser();
});

it('return list', function () {
    PageFactory::new()
        ->publicPublished()
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

it('show', function () {
    $page = PageFactory::new()
        ->publicPublished()
        ->createOne(['name' => 'My Page Title']);

    getJson('api/pages/'.$page->getRouteKey())
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($page) {
            $json
                ->where('data.type', 'pages')
                ->where('data.id', Str::slug($page->name))
                ->where('data.attributes.name', $page->name)
                ->etc();
        });
});
