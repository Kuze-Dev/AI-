<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Collection;

use App\HttpTenantApi\Resources\CollectionEntryResource;
use Domain\Collection\Enums\PublishBehavior;
use Domain\Collection\Models\Builders\CollectionEntryBuilder;
use Domain\Collection\Models\Collection;
use Domain\Collection\Models\CollectionEntry;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[ApiResource('collections.entries', only: ['index', 'show'], parameters: ['entries' => 'collectionEntry'])]
class CollectionEntryController
{
    public function index(Collection $collection): JsonApiResourceCollection
    {
        return CollectionEntryResource::collection(
            QueryBuilder::for(
                $collection->collectionEntries()
            )
                ->allowedFilters([
                    'title',
                    'slug',
                    AllowedFilter::callback(
                        'publish_status',
                        fn (CollectionEntryBuilder $query, $value) => $query->wherePublishStatus(PublishBehavior::tryFrom($value))
                    ),
                    AllowedFilter::callback(
                        'date_range',
                        fn (CollectionEntryBuilder $query, $value) => $query->whereDateRange($value)
                    ),
                    AllowedFilter::callback(
                        'year',
                        fn (CollectionEntryBuilder $query, $value) => $query->whereEntryYear($value)
                    ),
                    AllowedFilter::callback(
                        'month',
                        fn (CollectionEntryBuilder $query, $value) => $query->whereEntryMonth($value)
                    ),
                    AllowedFilter::callback(
                        'taxonomy',
                        fn (CollectionEntryBuilder $query, $value) => $query->whereTaxonomyTerm($value)
                    )
                ])
                ->allowedSorts([
                    'order',
                    'title',
                    'published_at',
                ])
                ->jsonPaginate()
        );
    }

    public function show(string $collection, string $collectionEntry): CollectionEntryResource
    {
        return CollectionEntryResource::make(
            QueryBuilder::for(
                CollectionEntry::whereSlug($collectionEntry)
                    ->whereRelation('collection', 'slug', $collection)
            )
                ->allowedIncludes([
                    'taxonomyTerms',
                    'slugHistories',
                    'metaData',
                ])
                ->firstOrFail()
        );
    }
}
