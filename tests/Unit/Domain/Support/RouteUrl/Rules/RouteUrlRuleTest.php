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
use Illuminate\Validation\InvokableValidationRule;
use Tests\Fixtures\TestModelForRouteUrl;

use function Pest\Laravel\assertDatabaseEmpty;
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
        routeUrlRule(TestModelForRouteUrl::class, null)
            ->passes('attribute', 'test')
    );
});

it('edit current data w/o modifying is passed', function () {
    $model = TestModelForRouteUrl::create([
        'name' => 'my-awesome-name',
    ]);

    app(CreateOrUpdateRouteUrlAction::class)
        ->execute($model, new RouteUrlData(null));

    assertTrue(
        routeUrlRule($model::class, $model)
            ->passes('attribute', $model->getActiveRouteUrl()->url)
    );
});

function routeUrlRule(string $modelUsed, ?HasRouteUrl $model): InvokableValidationRule
{
    return InvokableValidationRule::make(new RouteUrlRule($modelUsed, $model));
}
