<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Content\V2;

use App\Features\CMS\CMSBase;
use App\HttpTenantApi\Resources\ContentEntryResource;
use Domain\Content\Enums\PublishBehavior;
use Domain\Content\Models\Builders\ContentEntryBuilder;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Prefix;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    Prefix('v2'),
    Middleware(['feature.tenant:'.CMSBase::class, 'auth:sanctum'])
]
class ContentEntryV2Controller
{
    #[Get('/contents/{content}/entries', name: 'v2.contents.entries.index')]
    public function index(Content $content): JsonApiResourceCollection
    {
        return ContentEntryResource::collection(
            QueryBuilder::for($content->contentEntries()
                ->where('status', true)
                ->with(['content.blueprint', 'activeRouteUrl', 'blueprintData']))
                ->allowedFilters([
                    'title',
                    'slug',
                    AllowedFilter::exact('locale'),
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
                    AllowedFilter::callback('data', function (Builder $query, string $value) {
                        $query->whereRaw('JSON_SEARCH(data, "all", ?) IS NOT NULL', [$value]);
                    }),
                    AllowedFilter::callback('search_data', function (Builder $query, string $value) {
                        $query->whereRaw('CAST(data AS CHAR) LIKE ?', ['%'.$value.'%']);
                    }),
                    AllowedFilter::exact('sites.id'),
                ])
                ->allowedSorts([
                    'order',
                    'title',
                    'created_at',
                    'updated_at',
                    'published_at',
                ])
                ->allowedIncludes([
                    'taxonomyTerms.taxonomy',
                    'routeUrls',
                    'metaData',
                    'blueprintData.media',
                    'dataTranslation',
                    'parentTranslation',
                ])
                ->jsonPaginate()
        );
    }

    #[Get('/contents/{content}/entries/{contentEntry}', name: 'v2.contents.entries.show')]
    public function show(string $content, string $contentEntry): ContentEntryResource
    {
        return ContentEntryResource::make(
            QueryBuilder::for(
                ContentEntry::whereSlug($contentEntry)
                    ->where('status', true)
                    ->whereRelation('content', 'slug', $content)
            )
                ->allowedIncludes([
                    'content',
                    'taxonomyTerms.taxonomy',
                    'routeUrls',
                    'metaData',
                    'blueprintData',
                    'dataTranslation',
                    'parentTranslation',
                ])
                ->firstOrFail()
        );
    }
}
