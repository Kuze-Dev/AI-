<?php

declare(strict_types=1);

use Domain\Content\Database\Factories\ContentEntryFactory;
use Domain\Content\Database\Factories\ContentFactory;
use Domain\Page\Database\Factories\PageFactory;
use Domain\Page\Enums\Visibility;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Testing\Fluent\AssertableJson;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;
use Support\RouteUrl\Contracts\HasRouteUrl;
use Support\RouteUrl\Database\Factories\RouteUrlFactory;
use Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Tests\Fixtures\TestModelForRouteUrl;

use function Pest\Laravel\getJson;
use function Pest\Laravel\withoutExceptionHandling;

uses()->group('route_url');

beforeEach(function () {
    testInTenantContext();
});

it('can retrieve model at requested route url', function (HasRouteUrl $model, string $route) {
    getJson('api/route'.$route)
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->has('data.type')
                ->has('data.id')
                ->has('data.attributes')
                ->etc()
        );
})->with([
    [
        fn () => PageFactory::new([
            'visibility' => Visibility::PUBLIC,
        ])
            ->published()
            ->has(RouteUrlFactory::new(['url' => '/test/page']))
            ->createOne(),
        '/test/page',
    ],
    [
        fn () => ContentEntryFactory::new()
            ->for(
                ContentFactory::new()
                    ->withDummyBlueprint()
                    ->createOne()
            )
            ->has(RouteUrlFactory::new(['url' => '/test/content/entry']))
            ->createOne(),
        '/test/content/entry',
    ],
])->only();

it('can retrieve model using inactive route url', function () {
    $page = PageFactory::new()
        ->published()
        ->has(
            RouteUrlFactory::new()
                ->count(2)
                ->sequence(
                    ['url' => '/old/route/url'],
                    ['url' => '/new/route/url'],
                )
        )
        ->createOne();

    getJson('api/route/old/route/url')
        ->assertOk()
        ->assertJson(
            fn (AssertableJson $json) => $json
                ->where('data.type', 'pages')
                ->where('data.id', $page->getRouteKey())
                ->etc()
        );
});

it('responds 404 when route url doesn\'t exist', function () {
    getJson('api/route/'.fake()->word())
        ->assertNotFound();
});

it('can get route url but return InvalidArgumentException with error message', function () {
    DB::connection()
        ->getSchemaBuilder()
        ->create((new TestModelForRouteUrl)->getTable(), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('draftable_id')->nullable();
            $table->timestamps();
        });

    Relation::morphMap([TestModelForRouteUrl::class]);

    $model = TestModelForRouteUrl::create(['name' => 'my-awesome-name']);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('url/path/one', true));

    withoutExceptionHandling();
    getJson('api/route/'.$model->refresh()->activeRouteUrl->url)
        ->assertOk();
})
    ->throws(InvalidArgumentException::class, 'No resource found for model '.TestModelForRouteUrl::class);
