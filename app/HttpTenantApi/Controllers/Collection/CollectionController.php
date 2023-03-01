<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Collection;

use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Domain\Collection\Models\Collection;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;
use App\HttpTenantApi\Resources\CollectionResource;

#[ApiResource('collections', only: ['index', 'show'])]
class CollectionController
{
    public function index(): JsonApiResourceCollection
    {
        return CollectionResource::collection(
            QueryBuilder::for(Collection::class)
                ->allowedIncludes(['taxonomies'])
                ->allowedFilters(['name', 'slug', AllowedFilter::exact('sites.id')])
                ->jsonPaginate()
        );
    }

    public function show(string $collection): CollectionResource
    {
        return CollectionResource::make(
            QueryBuilder::for(Collection::whereSlug($collection))
                ->allowedIncludes([
                    'taxonomies',
                    'slugHistories',
                ])
                ->firstOrFail()
        );
    }
}
