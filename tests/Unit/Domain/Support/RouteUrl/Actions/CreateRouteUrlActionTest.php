<?php

declare(strict_types=1);

use Domain\Support\RouteUrl\Actions\UpdateOrCreateRouteUrlAction;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;
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
            $table->timestamps();
        });

    Relation::morphMap([TestModelForRouteUrl::class]);
    assertDatabaseEmpty(RouteUrl::class);
});

it('create w/o specify route_url', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(UpdateOrCreateRouteUrlAction::class)
        ->execute($model, new RouteUrlData(null));

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
        'name' => 'my-awesome-name',
    ]);

    app(UpdateOrCreateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('my-awesome-name'));

    assertDatabaseCount(RouteUrl::class, 1);
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => 'my-awesome-name',
        'is_override' => true,
    ]);
});

it('create w/ specify route_url', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my awesome name',
    ]);

    app(UpdateOrCreateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('im-custom-value'));

    assertDatabaseCount(RouteUrl::class, 1);
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => 'im-custom-value',
        'is_override' => true,
    ]);
});

it('reduplicate previous route_url', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(UpdateOrCreateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('my-awesome-name'));

    $model->refresh();

    app(UpdateOrCreateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('im-custom-value'));

    $model->refresh();

    app(UpdateOrCreateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('my-awesome-name'));

    assertDatabaseCount(RouteUrl::class, 2);

    assertDatabaseHas(RouteUrl::class, [
        'id' => 2,
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => 'im-custom-value',
        'is_override' => true,
    ]);
    assertDatabaseHas(RouteUrl::class, [
        'id' => 3,
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => 'my-awesome-name',
        'is_override' => true,
    ]);
});

test('override', function (?string $data) {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(UpdateOrCreateRouteUrlAction::class)
        ->execute($model, new RouteUrlData($data));

    assertDatabaseCount(RouteUrl::class, 1);
    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'is_override' => filled($data),
    ]);
})
    ->with([
        '', null, 'data',
    ]);
