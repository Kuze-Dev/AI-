<?php

declare(strict_types=1);

use Domain\Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Tests\Fixtures\TestModelForRouteUrl;

use Tests\Fixtures\TestSecondModelForRouteUrl;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;
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

it('can transfer non active url to another model', function () {
    DB::connection()
        ->getSchemaBuilder()
        ->create((new TestSecondModelForRouteUrl())->getTable(), function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    Relation::morphMap([TestSecondModelForRouteUrl::class]);

    $modelA = TestModelForRouteUrl::create(['name' => 'one']);
    $modelB = TestSecondModelForRouteUrl::create(['name' => 'two']);

    $initialModelARouteUrl = app(CreateOrUpdateRouteUrlAction::class)
        ->execute($modelA, new RouteUrlData('url-one', true));

    travelTo(now()->addSecond());

    $newModelARouteUrl = app(CreateOrUpdateRouteUrlAction::class)
        ->execute($modelA, new RouteUrlData('url-one-new', true));

    travelTo(now()->addSecond());

    $reinstatedRouteUrl = app(CreateOrUpdateRouteUrlAction::class)
        ->execute($modelB, new RouteUrlData('url-one', true));

    assertTrue($modelB->activeRouteUrl()->is($reinstatedRouteUrl));
    assertTrue($initialModelARouteUrl->is($reinstatedRouteUrl));
});
