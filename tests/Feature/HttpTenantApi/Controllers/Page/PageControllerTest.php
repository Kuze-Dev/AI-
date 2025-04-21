<?php

declare(strict_types=1);

use Domain\Page\Database\Factories\BlockFactory;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Site\Database\Factories\SiteFactory;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Support\RouteUrl\Database\Factories\RouteUrlFactory;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('can list pages', function () {
    PageFactory::new()
        ->addBlockContent(BlockFactory::new()->withDummyBlueprint())
        ->published()
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
        ->addBlockContent(BlockFactory::new()->withDummyBlueprint())
        ->published()
        ->count(2)
        ->sequence(
            ['name' => 'Foo', 'visibility' => 'authenticated', 'locale' => 'jp'],
            ['name' => 'Bar', 'visibility' => 'guest', 'locale' => 'fr'],
            ['name' => 'Example', 'visibility' => 'public', 'locale' => 'de']
        )
        ->create();

    foreach ($pages as $page) {
        getJson('api/pages?'.http_build_query(['filter' => [$attribute => $page->$attribute]]))
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
})->with(['name', 'slug', 'visibility', 'locale']);

it('can show a page with includes', function (string $include) {
    $page = PageFactory::new()
        ->addBlockContent(BlockFactory::new()->withDummyBlueprint())
        ->has(RouteUrlFactory::new())
        ->published()
        ->createOne();

    $page->metaData()->create([
        'title' => $page->name,
        'description' => 'Foo description',
        'author' => 'Foo author',
        'keywords' => 'Foo keywords',
    ]);

    getJson("api/pages/{$page->getRouteKey()}?".http_build_query(['include' => $include]))
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($page, $include) {
            $json
                ->where('data.type', 'pages')
                ->where('data.id', Str::slug($page->name))
                ->where('data.attributes.name', $page->name)
                ->where('data.attributes.route_url', $page->activeRouteUrl->url)
                ->has(
                    'included',
                    callback: fn (AssertableJson $json) => $json->where('type', $include)->etc()
                )
                ->etc();
        });
})->with([
    'blockContents',
    'routeUrls',
    'metaData',
]);

it('cant show an unpublished page', function () {
    $page = PageFactory::new()
        ->createOne();

    getJson("api/pages/{$page->getRouteKey()}")
        ->assertStatus(412);
});

it('can show an unpublished page with valid signature', function () {
    $page = PageFactory::new()
        ->createOne();

    $queryString = Str::after(URL::temporarySignedRoute('tenant.api.pages.show', now()->addMinutes(15), [$page->getRouteKey()], false), '?');

    getJson("api/pages/{$page->getRouteKey()}?{$queryString}")->assertOk()
        ->assertJson(function (AssertableJson $json) use ($page) {
            $json
                ->where('data.type', 'pages')
                ->where('data.id', Str::slug($page->name))
                ->etc();
        });
});

it('can list pages of specific site', function () {
    $site = SiteFactory::new()->createOne();
    PageFactory::new()
        ->published()
        ->hasAttached($site)
        ->count(1)
        ->create();
    PageFactory::new()
        ->published()
        ->count(2)
        ->create();

    getJson("api/pages?filter[sites.id]={$site->id}")
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->count('data', 1)
                ->etc();
        });
});
