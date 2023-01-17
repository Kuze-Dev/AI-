<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Collection;

use App\HttpTenantApi\Resources\CollectionResource;
use Domain\Collection\Models\Collection;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

// [list] /api/collections
// [show] /api/collections/:collection
// [list] /api/collections/:collection/entries
// [show] /api/collections/:collection/entries/:entry

#[ApiResource('collections', only: ['index', 'show'])]
class CollectionController
{
    public function index(): JsonApiResourceCollection
    {
        return CollectionResource::collection(
            QueryBuilder::for(Collection::query()->select(['name', 'slug']))
                ->allowedIncludes(['blueprint', 'taxonomies','collectionEntries'])
                ->allowedFilters(['name', 'slug'])
                ->jsonPaginate()
        );
    }

    public function show(Collection $collection): CollectionResource
    {
        return CollectionResource::make($collection);
    }
}
