<?php

declare(strict_types=1);

use Domain\Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Domain\Support\RouteUrl\Support;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\TestModelForRouteUrl;

use Tests\Fixtures\TestSecondModelForRouteUrl;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function PHPUnit\Framework\assertFalse;
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

it('return false when empty', function () {
    assertFalse(Support::isActiveRouteUrl('xxx'));
});

it('return true', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData(null));

    assertDatabaseCount(RouteUrl::class, 1);

    assertTrue(Support::isActiveRouteUrl($model->getActiveRouteUrl()->url));
});

it('determined if active url', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('old-url'));

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('new-url'));

    assertDatabaseCount(RouteUrl::class, 2);

    assertFalse(Support::isActiveRouteUrl('old-url'));
    assertTrue(Support::isActiveRouteUrl('new-url'));
});

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
        ->execute($model1, new RouteUrlData('url-one'));

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model2, new RouteUrlData('url-two'));

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model1, new RouteUrlData('url-one-new'));

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model2, new RouteUrlData('url-one'));

    assertDatabaseCount(RouteUrl::class, 3);

    assertTrue(Support::isActiveRouteUrl('url-one-new'));
    assertTrue(Support::isActiveRouteUrl('url-one'));
    assertFalse(Support::isActiveRouteUrl('url-two'));
});
