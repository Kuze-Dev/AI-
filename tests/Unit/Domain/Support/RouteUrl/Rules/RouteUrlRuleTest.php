<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;
use Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Support\RouteUrl\Rules\UniqueActiveRouteUrlRule;
use Tests\Fixtures\TestModelForRouteUrl;

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

it('passes when valid', function () {
    $result = Validator::make(
        ['url' => 'test'],
        ['url' => new UniqueActiveRouteUrlRule]
    )->passes();

    assertTrue($result);
});

it('passes with ignored model', function () {
    $model = TestModelForRouteUrl::create(['name' => 'my-awesome-name']);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('test', false));

    $result = Validator::make(
        ['url' => $model->activeRouteUrl->url],
        ['url' => new UniqueActiveRouteUrlRule($model)]
    )->passes();

    assertTrue($result);
});

it('passes with non active url', function () {
    $model = TestModelForRouteUrl::create(['name' => 'my-awesome-name']);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('old-one', true));

    travelTo(now()->addSecond());

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model->refresh(), new RouteUrlData('one', true));

    $result = Validator::make(
        ['url' => 'old-one'],
        ['url' => new UniqueActiveRouteUrlRule]
    )->passes();

    assertTrue($result);
});
