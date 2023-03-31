<?php

declare(strict_types=1);

use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Tests\Fixtures\TestModelForRouteUrl;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;

uses()->group('route_url');

beforeEach(function () {
    testInTenantContext();

    DB::connection()
        ->getSchemaBuilder()
        ->create((new TestModelForRouteUrl())->getTable(), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->routeUrl();
            $table->string('slug')->unique();
            $table->timestamps();
        });

    Relation::morphMap([TestModelForRouteUrl::class]);
    assertDatabaseEmpty(RouteUrl::class);
});

it('create w/o specify route_url', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my awesome name',
    ]);

    expect($model)
        ->getRouteUrlUrl()
        ->toBe('my-awesome-name')
        ->getRouteUrlIsOverride()
        ->toBe(false);

    assertDatabaseCount(RouteUrl::class, 1);
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => 'my-awesome-name',
        'is_override' => false,
    ]);
});
it('create w/ specify route_url but same as default', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my awesome name',
        TestModelForRouteUrl::getRouteUrlUrlColumn() => 'my-awesome-name',
    ]);

    expect($model)
        ->getRouteUrlUrl()
        ->toBe('my-awesome-name')
        ->getRouteUrlIsOverride()
        ->toBe(false);

    assertDatabaseCount(RouteUrl::class, 1);
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => 'my-awesome-name',
        'is_override' => false,
    ]);
});

it('create w/ specify route_url', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my awesome name',
        TestModelForRouteUrl::getRouteUrlUrlColumn() => 'im custom value',
    ]);

    expect($model)
        ->getRouteUrlUrl()
        ->toBe('im custom value')
        ->getRouteUrlIsOverride()
        ->toBe(true);

    assertDatabaseCount(RouteUrl::class, 1);
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => 'im custom value',
        'is_override' => true,
    ]);
});
