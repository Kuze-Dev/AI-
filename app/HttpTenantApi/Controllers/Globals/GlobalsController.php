<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Globals;

use App\Features\CMS\CMSBase;
use App\HttpTenantApi\Resources\GlobalsResource;
use Domain\Globals\Models\Globals;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('globals', only: ['index', 'show']),
    Middleware('feature.tenant:'.CMSBase::class)
]
class GlobalsController
{
    public function index(): JsonApiResourceCollection
    {
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
