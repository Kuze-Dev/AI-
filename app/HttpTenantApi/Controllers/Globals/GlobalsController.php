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
            QueryBuilder::for(Globals::query())
                ->allowedFilters(['name', 'slug'])
                ->jsonPaginate()
        );
    }

    public function show(string $global): GlobalsResource
    {
        /** @var Globals */
        $global = QueryBuilder::for(Globals::whereSlug($global))
            ->allowedIncludes([
                'slugHistories',
            ])
            ->firstOrFail();

        return GlobalsResource::make($global);
    }
}
