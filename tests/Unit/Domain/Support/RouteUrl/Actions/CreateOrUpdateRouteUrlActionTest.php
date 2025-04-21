<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;
use Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Support\RouteUrl\Models\RouteUrl;
use Tests\Fixtures\TestModelForRouteUrl;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;
use function PHPUnit\Framework\assertTrue;

uses()->group('route_url');

beforeEach(function () {
    testInTenantContext();

    DB::connection()
        ->getSchemaBuilder()
        ->create((new TestModelForRouteUrl)->getTable(), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

    Relation::morphMap([TestModelForRouteUrl::class]);
});

it('can create generated route_url', function () {
    $model = TestModelForRouteUrl::create(['name' => 'my awesome name']);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('', false));

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => '/my-awesome-name',
        'is_override' => false,
    ]);
});

it('will not create another route url when updating it to same as before', function () {
    $model = TestModelForRouteUrl::create(['name' => 'my awesome name']);

    $initialRouteUrl = app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('', false));

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => '/my-awesome-name',
        'is_override' => false,
    ]);

    travelTo(now()->addSecond());

    $newRouteUrl = app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData($model->activeRouteUrl->url, $model->activeRouteUrl->is_override));

    assertTrue($initialRouteUrl->is($newRouteUrl));
});

it('can create with overriden url', function () {
    $model = TestModelForRouteUrl::create(['name' => 'my awesome name']);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('some/custom/url', true));

    assertDatabaseHas(RouteUrl::class, [
        'model_type' => $model->getMorphClass(),
        'model_id' => $model->getKey(),
        'url' => '/some/custom/url',
        'is_override' => true,
    ]);
});

it('can reuse previous route_url', function () {
    $model = TestModelForRouteUrl::create(['name' => 'my awesome name']);

    $initialRouteUrl = app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('original-url', true));

    travelTo(now()->addSecond());

    $newRouteUrl = app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('new-url', true));

    assertTrue($model->activeRouteUrl()->is($newRouteUrl));

    travelTo(now()->addSecond());

    $reinstatedRouteUrl = app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('original-url', true));

    assertTrue($model->activeRouteUrl()->is($reinstatedRouteUrl));
    assertTrue($initialRouteUrl->is($reinstatedRouteUrl));
});
