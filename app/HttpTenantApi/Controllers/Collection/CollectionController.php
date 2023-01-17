<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Collection;

use App\HttpTenantApi\Resources\CollectionResource;
use Domain\Collection\Models\Collection;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[ApiResource('collections', only: ['index', 'show'])]
class CollectionController
{
    public function index(): JsonApiResourceCollection
    {
        return CollectionResource::collection(
            QueryBuilder::for(Collection::query())
                ->allowedIncludes(['taxonomies'])
                ->allowedFilters(['name', 'slug'])
                ->jsonPaginate()
        );
    }

    public function show(Collection $collection): CollectionResource
    {
        return CollectionResource::make($collection);
    }
}
