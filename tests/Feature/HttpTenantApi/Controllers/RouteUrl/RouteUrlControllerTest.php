<?php

declare(strict_types=1);

use Domain\Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Tests\Fixtures\TestModelForRouteUrl;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\getJson;

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

it('get 404', function () {
    getJson('api/route/'.fake()->word())
        ->assertNotFound();
});

it('can get route', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('my-awesome-name'));

    getJson('api/route/'.$model->getActiveRouteUrl()->url)
        ->assertOk();
});

it('can show a route with includes', function (string $include) {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('my-awesome-name'));

    getJson('api/route/'.$model->getActiveRouteUrl()->url.'?'.http_build_query(['include' => $include]))
        ->assertOk();
})
    ->with(['model'])->todo();
