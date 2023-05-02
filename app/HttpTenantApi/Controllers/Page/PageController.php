<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Page;

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

#[ApiResource('pages', only: ['index', 'show'])]
class PageController
{
    public function index(): JsonApiResourceCollection
    {
        return PageResource::collection(
            QueryBuilder::for(Page::with('activeRouteUrl'))
                ->allowedFilters([
                    'name',
                    'slug',
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
        $page = QueryBuilder::for(Page::whereSlug($page))
            ->allowedIncludes([
                'blockContents.block',
                'routeUrls',
                'metaData',
            ])
            ->firstOrFail();

        abort_if(is_null($page->published_at) && ! $this->generateNewRequestForSignedUrl($request)->hasValidSignature(), 412);

        return PageResource::make($page);
    }

    protected function generateNewRequestForSignedUrl(Request $request): Request
    {
        return Request::create($request->server('REQUEST_SCHEME')
            . '://' . $request->server('HTTP_HOST') . '/' . $request->path()
            . '?expires=' . $request->all()['expires'] . '&signature='
            . $request->all()['signature'], 'GET');
    }
}
