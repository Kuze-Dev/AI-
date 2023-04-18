<?php

declare(strict_types=1);

use Domain\Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Tests\Fixtures\TestModelForRouteUrl;

use Tests\Fixtures\TestSecondModelForRouteUrl;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertTrue;

uses()->group('route_url');

beforeEach(function () {
    testInTenantContext();

    DB::connection()
        ->getSchemaBuilder()
        ->create((new TestModelForRouteUrl())->getTable(), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

    Relation::morphMap([TestModelForRouteUrl::class]);
    assertDatabaseEmpty(RouteUrl::class);
});

it('create w/o default route_url', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my awesome name',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData($model::generateRouteUrl($model, $model->getAttributes()), false));

    assertDatabaseCount(RouteUrl::class, 1);
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => '/my-awesome-name',
        'is_override' => false,
    ]);
});

it('can update w/o modify route_url', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    // initialise route url
    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData($model::generateRouteUrl($model, $model->getAttributes()), false));
    $model->refresh();
    $model->wasRecentlyCreated = false;

    $routeUrlDBData = [
        'id' => 1,
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => '/my-awesome-name',
        'is_override' => false,
    ];

    assertDatabaseCount(RouteUrl::class, 1);
    assertDatabaseHas(RouteUrl::class, $routeUrlDBData);

    $oldRouteUrl = RouteUrl::first();

    travelTo(now()->addSecond());

    // update with same url
    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData($model->activeRouteUrl->url, $model->activeRouteUrl->is_override));

    assertDatabaseCount(RouteUrl::class, 1);
    assertDatabaseHas(RouteUrl::class, $routeUrlDBData);

    $newRouteUrl = RouteUrl::first();

    assertTrue($oldRouteUrl->is($newRouteUrl));

    assertEquals($oldRouteUrl->created_at->toString(), $newRouteUrl->created_at->toString());
    assertEquals($oldRouteUrl->updated_at->toString(), $newRouteUrl->updated_at->toString());
});

it('create w/ specify route_url but same as default', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData($model->name, false));

    assertDatabaseCount(RouteUrl::class, 1);
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => '/my-awesome-name',
        'is_override' => false,
    ]);
});

it('create w/ specify route_url', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my awesome name',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('im-custom-value', true));

    assertDatabaseCount(RouteUrl::class, 1);
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => '/im-custom-value',
        'is_override' => true,
    ]);
});

it('reuse previous route_url', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('original-url', true));

    $model->refresh();
    travelTo(now()->addSecond());

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('new-url', true));

    $model->refresh();
    travelTo(now()->addSecond());

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('original-url', true));

    assertDatabaseCount(RouteUrl::class, 2);

    assertDatabaseHas(RouteUrl::class, [
        'id' => 1,
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => '/original-url',
        'is_override' => true,
    ]);
    assertDatabaseHas(RouteUrl::class, [
        'id' => 2,
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => '/new-url',
        'is_override' => true,
    ]);
});

test('override', function (?string $data) {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute(
            $model,
            new RouteUrlData($data ?? '', filled($data))
        );

    assertDatabaseCount(RouteUrl::class, 1);
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'is_override' => filled($data),
        'url' => '/'.(filled($data) ? $data : $model::generateRouteUrl($model, $model->getAttributes())),
    ]);
})
    ->with([
        '', null, 'data',
    ]);

it('transfer a non active url', function () {
    DB::connection()
        ->getSchemaBuilder()
        ->create((new TestSecondModelForRouteUrl())->getTable(), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    Relation::morphMap([TestSecondModelForRouteUrl::class]);

    $model1 = TestModelForRouteUrl::create([
        'name' => 'one',
    ]);
    $model2 = TestSecondModelForRouteUrl::create([
        'name' => 'two',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model1, new RouteUrlData('url-one', true));

    $model1->refresh();
    travelTo(now()->addSecond());

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model2, new RouteUrlData('url-two', true));

    $model2->refresh();
    travelTo(now()->addSecond());

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model1, new RouteUrlData('url-one-new', true));

    $model1->refresh();
    travelTo(now()->addSecond());

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model2, new RouteUrlData('url-one', true));

    $model2->refresh();

    expect($model1)
        ->activeRouteUrl
        ->url
        ->toBe('/url-one-new');

    expect($model2)
        ->activeRouteUrl
        ->url
        ->toBe('/url-one');

    assertDatabaseCount(RouteUrl::class, 3);
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model1->getMorphClass(),
        'model_id' => $model1->getKey(),
        'url' => '/url-one-new',
    ]);
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model2->getMorphClass(),
        'model_id' => $model2->getKey(),
        'url' => '/url-one',
    ]);
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model2->getMorphClass(),
        'model_id' => $model2->getKey(),
        'url' => '/url-two',
    ]);
});
