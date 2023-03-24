<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Collection;

use App\HttpTenantApi\Resources\CollectionEntryResource;
use Carbon\Carbon;
use Domain\Collection\Enums\PublishBehavior;
use Domain\Collection\Models\Builders\CollectionEntryBuilder;
use Domain\Collection\Models\Collection;
use Domain\Collection\Models\CollectionEntry;
use Illuminate\Support\Arr;
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
                        'published_at_start',
                        fn (CollectionEntryBuilder $query, $value) => $query->wherePublishedAtRange(publishedAtStart: Carbon::parse($value))
                    ),
                    AllowedFilter::callback(
                        'published_at_end',
                        fn (CollectionEntryBuilder $query, $value) => $query->wherePublishedAtRange(publishedAtEnd: Carbon::parse($value))
                    ),
                    AllowedFilter::callback(
                        'published_at_year_month',
                        function (CollectionEntryBuilder $query, string|array $value) {
                            $value = Arr::wrap($value);

                            $year = (int) $value[0];
                            $month = filled($value[1] ?? null) ? (int) $value[1] : null;

                            $query->wherePublishedAtYearMonth($year, $month);
                        },
                    ),
                    AllowedFilter::callback(
                        'taxonomies',
                        function (CollectionEntryBuilder $query, array $value) {
                            foreach ($value as $taxonomySlug => $taxonomyTermSlugs) {
                                if (filled($taxonomyTermSlugs)) {
                                    $query->whereTaxonomyTerms($taxonomySlug, Arr::wrap($taxonomyTermSlugs));
                                }
                            }
                        }
                    ),
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
