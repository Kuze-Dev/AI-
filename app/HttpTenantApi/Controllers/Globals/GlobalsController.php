<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Globals;

use Domain\Globals\Models\Globals;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\HttpTenantApi\Resources\GlobalsResource;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[ApiResource('globals', only: ['index', 'show'])]
class GlobalsController
{
    public function index(): JsonApiResourceCollection
    {
        return GlobalsResource::collection(
            QueryBuilder::for(Globals::with('blueprint'))
                ->allowedFilters(['name', 'slug', AllowedFilter::exact('sites.id')])
                ->allowedIncludes('blueprint')
                ->jsonPaginate()
        );
    }

    public function show(string $global): GlobalsResource
    {
        /** @var Globals */
        $global = QueryBuilder::for(Globals::whereSlug($global))
            ->firstOrFail();

        return GlobalsResource::make($global);
    }
}
