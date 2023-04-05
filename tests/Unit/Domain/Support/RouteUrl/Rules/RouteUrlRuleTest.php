<?php

declare(strict_types=1);

use Domain\Support\RouteUrl\Actions\CreateOrUpdateRouteUrlAction;
use Domain\Support\RouteUrl\Contracts\HasRouteUrl;
use Domain\Support\RouteUrl\DataTransferObjects\RouteUrlData;
use Domain\Support\RouteUrl\Models\RouteUrl;
use Domain\Support\RouteUrl\Rules\RouteUrlRule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\Fixtures\TestModelForRouteUrl;
use Illuminate\Validation\Validator;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;

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

it('passed w/o any', function () {
    assertTrue(
        routeUrlRule(TestModelForRouteUrl::class, null, 'test')
    );
});

it('can edit current data w/o modifying is passed', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData(null));

    assertTrue(
        routeUrlRule($model::class, $model, $model->getActiveRouteUrl()->url)
    );
});

it('passed when using old history of ur on create', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('old-one'));

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('one'));

    assertTrue(
        routeUrlRule($model::class, null, 'old-one')
    );
});

it('passed when using old history of ur on update', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('old-one'));

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('one'));

    assertTrue(
        routeUrlRule($model::class, $model, 'old-one')
    );
});

it('fail on duplicate active url on create', function (bool $hasHistory) {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    if ($hasHistory) {
        app(CreateOrUpdateRouteUrlAction::class)
            ->execute($model, new RouteUrlData('old-one'));
    }

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('one'));

    assertFalse(
        routeUrlRule($model::class, null, 'one')
    );
})
    ->with(['has History' => true, 'has no history' => false]);

it('fail on duplicate active url on update', function (bool $hasHistory) {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);
    if ($hasHistory) {
        app(CreateOrUpdateRouteUrlAction::class)
            ->execute($model, new RouteUrlData('old-one'));
    }

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData('one'));

    $model2 = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model2, new RouteUrlData('two'));

    assertFalse(
        routeUrlRule($model::class, $model2, 'one')
    );
})
    ->with(['has History' => true, 'has no history' => false]);

/**
 *  if $model is null, it means it uses on create, else on edit
 */
function routeUrlRule(string $modelUsed, ?HasRouteUrl $model, string $dataToBeValidate): bool
{
    $r = new Validator(
        new Translator(
            new ArrayLoader(),
            'en'
        ),
        ['data' => $dataToBeValidate],
        ['data' => new RouteUrlRule($modelUsed, $model)]
    );

    return $r->passes();
}
