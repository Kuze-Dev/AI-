<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Page;

use App\Features\CMS\CMSBase;
use App\HttpTenantApi\Resources\PageResource;
use Carbon\Carbon;
use Domain\Page\Models\Builders\PageBuilder;
use Domain\Page\Models\Page;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Spatie\RouteAttributes\Attributes\Middleware;

#[
    ApiResource('pages', only: ['index', 'show']),
    Middleware('feature.tenant:'. CMSBase::class)
]
class PageController
{
    public function index(): JsonApiResourceCollection
    {
        return PageResource::collection(
            QueryBuilder::for(
                Page::with('activeRouteUrl')
                    ->whereNotNull('published_at')
            )
                ->allowedFilters([
                    'name',
                    'slug',
                    'visibility',
                    AllowedFilter::callback(
                        'published_at_start',
                        fn (PageBuilder $query, $value) => $query->wherePublishedAtRange(publishedAtStart: Carbon::parse($value))
                    ),
                    AllowedFilter::callback(
                        'published_at_end',
                        fn (PageBuilder $query, $value) => $query->wherePublishedAtRange(publishedAtEnd: Carbon::parse($value))
                    ),
                    AllowedFilter::callback(
                        'published_at_year_month',
                        function (PageBuilder $query, string|array $value) {
                            $value = Arr::wrap($value);

                            $year = (int) $value[0];
                            $month = filled($value[1] ?? null) ? (int) $value[1] : null;

                            $query->wherePublishedAtYearMonth($year, $month);
                        },
                    ),
                    AllowedFilter::exact('sites.id')
                ])
                ->allowedIncludes([
                    'blockContents.block',
                    'routeUrls',
                    'metaData',
                ])
                ->jsonPaginate()
        );
    }

    public function show(Request $request, string $page): PageResource
    {
        /** @var Page $page */
        $page = QueryBuilder::for(Page::whereSlug($page))
            ->allowedIncludes([
                'blockContents.block',
                'routeUrls',
                'metaData',
            ])
            ->firstOrFail();

        $ignoreQuery = array_diff(array_keys($request->query->all()), ['signature', 'expires']);

        abort_if($page->isPublished() && ! URL::hasValidSignature($request, false, $ignoreQuery), 412);

        return PageResource::make($page);
    }
}
