<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Globals;

use App\Features\CMS\CMSBase;
use App\Http\Middleware\TenantApiAuthorizationMiddleware;
use App\HttpTenantApi\Controllers\BaseCms\BaseCmsController;
use App\HttpTenantApi\Resources\GlobalsResource;
use Domain\Globals\Models\Globals;
use Domain\Tenant\Support\ApiAbilitties;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('globals', only: ['index', 'show']),
    Middleware(['feature.tenant:'.CMSBase::class, TenantApiAuthorizationMiddleware::class])
]
class GlobalsController extends BaseCmsController
{
    public function index(): JsonApiResourceCollection
    {
        $this->checkAbilities(ApiAbilitties::global_view->value);

        return GlobalsResource::collection(
            QueryBuilder::for(Globals::with('blueprint'))
                ->allowedFilters(
                    [
                        'name',
                        'slug',
                        AllowedFilter::exact('locale'),
                        AllowedFilter::exact('sites.id'),
                        AllowedFilter::callback('data', function (Builder $query, string $value) {
                            $query->whereRaw('JSON_SEARCH(data, "all", ?) IS NOT NULL', [$value]);
                        }),
                        AllowedFilter::callback('search_data', function (Builder $query, string $value) {
                            $query->whereRaw('CAST(data AS CHAR) LIKE ?', ['%'.$value.'%']);
                        }),
                    ]
                )
                ->allowedIncludes(
                    [
                        'blueprint',
                        'blueprintData',
                        'dataTranslation',
                        'parentTranslation',
                    ])
                ->jsonPaginate()
        );
    }

    public function show(string $global): GlobalsResource
    {
        $this->checkAbilities(ApiAbilitties::global_view->value);

        return GlobalsResource::make(
            QueryBuilder::for(Globals::whereSlug($global))
                ->allowedIncludes(
                    [
                        'blueprint',
                        'blueprintData',
                        'dataTranslation',
                        'parentTranslation',
                    ])
                ->firstOrFail()
        );
    }
}
