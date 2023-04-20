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
use function Pest\Laravel\withoutExceptionHandling;

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

it('can get route url but return InvalidArgumentException with error message', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('url/path/one', true));

    withoutExceptionHandling();
    getJson('api/route/'.$model->refresh()->activeRouteUrl->url)
        ->assertOk();
})
    ->throws(
        InvalidArgumentException::class,
        'No resource found for model '.TestModelForRouteUrl::class
    );
