<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Collection;

use App\HttpTenantApi\Resources\CollectionEntryResource;
use Domain\Collection\Models\Collection;
use Domain\Collection\Models\CollectionEntry;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[ApiResource('collection.collectionEntries', only: ['index', 'show'], parameters: ['collectionEntries' => 'collectionEntries'])]
class CollectionEntryController
{
    public function index(Collection $collection): JsonApiResourceCollection
    {
        return CollectionEntryResource::collection(
            QueryBuilder::for($collection->collectionEntries()
                ->select(['title','slug','data', 'collection_id', 'order', 'published_at']))
                ->allowedFilters(['title','slug','order','published_at'])
                ->jsonPaginate()
        );
    }

    public function show(Collection $collection, CollectionEntry $collectionEntry): CollectionEntryResource
    {
        return CollectionEntryResource::make($collectionEntry);
    }
}