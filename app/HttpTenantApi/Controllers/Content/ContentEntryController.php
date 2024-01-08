<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Content;

use App\Features\CMS\CMSBase;
use App\HttpTenantApi\Resources\ContentEntryResource;
use Domain\Content\Enums\PublishBehavior;
use Domain\Content\Models\Builders\ContentEntryBuilder;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('contents.entries', only: ['index', 'show'], parameters: ['entries' => 'contentEntry']),
    Middleware('feature.tenant:'.CMSBase::class)
]
class ContentEntryController
{
    public function index(Content $content): JsonApiResourceCollection
    {
        return ContentEntryResource::collection(
            QueryBuilder::for($content->contentEntries()->with('content.blueprint', 'activeRouteUrl'))
                ->allowedFilters([
                    'title',
                    'slug',
                    AllowedFilter::callback(
                        'publish_status',
                        fn (ContentEntryBuilder $query, $value) => $query->wherePublishStatus(PublishBehavior::tryFrom($value))
                    ),
                    AllowedFilter::callback(
                        'published_at_start',
                        fn (ContentEntryBuilder $query, $value) => $query->wherePublishedAtRange(publishedAtStart: Carbon::parse($value))
                    ),
                    AllowedFilter::callback(
                        'published_at_end',
                        fn (ContentEntryBuilder $query, $value) => $query->wherePublishedAtRange(publishedAtEnd: Carbon::parse($value))
                    ),
                    AllowedFilter::callback(
                        'published_at_year_month',
                        function (ContentEntryBuilder $query, string|array $value) {
                            $value = Arr::wrap($value);

                            $year = (int) $value[0];
                            $month = filled($value[1] ?? null) ? (int) $value[1] : null;

                            $query->wherePublishedAtYearMonth($year, $month);
                        },
                    ),
                    AllowedFilter::callback(
                        'taxonomies',
                        function (ContentEntryBuilder $query, array $value) {
                            foreach ($value as $taxonomySlug => $taxonomyTermSlugs) {
                                if (filled($taxonomyTermSlugs)) {
                                    $query->whereTaxonomyTerms($taxonomySlug, Arr::wrap($taxonomyTermSlugs));
                                }
                            }
                        }
                    ),
                    AllowedFilter::exact('sites.id'),
                ])
                ->allowedSorts([
                    'order',
                    'title',
                    'published_at',
                ])
                ->allowedIncludes([
                    'taxonomyTerms.taxonomy',
                    'routeUrls',
                    'metaData',
                    'blueprintData',
                ])
                ->jsonPaginate()
        );
    }

    public function show(string $content, string $contentEntry): ContentEntryResource
    {
        return ContentEntryResource::make(
            QueryBuilder::for(
                ContentEntry::whereSlug($contentEntry)
                    ->whereRelation('content', 'slug', $content)
            )
                ->allowedIncludes([
                    'content',
                    'taxonomyTerms.taxonomy',
                    'routeUrls',
                    'metaData',
                    'blueprintData',
                ])
                ->firstOrFail()
        );
    }
}
