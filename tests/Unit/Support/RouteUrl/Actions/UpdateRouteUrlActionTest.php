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

it('update w/o specify route_url', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my awesome name',
    ]);

    $model->update([
        'name' => 'new name',
    ]);

    $model->refresh();

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

it('update w/ specify route_url ', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my awesome name',
    ]);

    $model->update([
        'name' => 'new name',
        $model::getRouteUrlUrlColumn() => 'new-route-url',
    ]);

    $model->refresh();

    expect($model)
        ->getRouteUrlUrl()
        ->toBe('new-route-url')
        ->getRouteUrlIsOverride()
        ->toBe(true);

    assertDatabaseCount(RouteUrl::class, 2);
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => 'my-awesome-name',
        'is_override' => false,
    ]);
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => 'new-route-url',
        'is_override' => true,
    ]);
});

it('update w/ default w/ modify then back to default')
    ->todo();
