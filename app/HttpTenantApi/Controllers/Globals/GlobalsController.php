<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Globals;

use App\HttpTenantApi\Resources\GlobalsResource;
use Domain\Globals\Models\Globals;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[ApiResource('globals', only: ['index', 'show'])]
class GlobalsController
{
    public function index(): JsonApiResourceCollection
    {
        return GlobalsResource::collection(
            QueryBuilder::for(Globals::with('blueprint'))
                ->allowedFilters(['name', 'slug'])
                ->allowedIncludes('blueprint')
                ->jsonPaginate()
        );
    }

    public function show(string $global): GlobalsResource
    {
        return GlobalsResource::make(
            QueryBuilder::for(Globals::whereSlug($global))
                ->allowedIncludes('blueprint')
                ->firstOrFail()
        );
    }
}
