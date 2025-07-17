<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Page;

use App\Features\CMS\CMSBase;
use App\Http\Middleware\TenantApiAuthorizationMiddleware;
use App\HttpTenantApi\Controllers\BaseCms\BaseCmsController;
use App\HttpTenantApi\Resources\PageResource;
use Domain\Page\Models\Builders\PageBuilder;
use Domain\Page\Models\Page;
use Domain\Tenant\Support\ApiAbilitties;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\RouteAttributes\Attributes\ApiResource;
use Spatie\RouteAttributes\Attributes\Middleware;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

#[
    ApiResource('pages', only: ['index', 'show']),
    Middleware(['feature.tenant:'.CMSBase::class, TenantApiAuthorizationMiddleware::class])
]
class PageController extends BaseCmsController
{
    public function index(): JsonApiResourceCollection
    {
        $this->checkAbilities(ApiAbilitties::page_view->value);

        return PageResource::collection(
            QueryBuilder::for(
                Page::with(['activeRouteUrl'])
                    ->whereNotNull('published_at')
            )
                ->allowedFilters([
                    'name',
                    'slug',
                    'visibility',
                    AllowedFilter::exact('locale'),
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
                    AllowedFilter::exact('sites.id'),
                ])
                ->allowedIncludes([
                    'blockContents.block',
                    'blockContents.blueprintData',
                    'dataTranslation',
                    'parentTranslation',
                    'routeUrls',
                    'metaData',
                ])
                ->jsonPaginate()
        );
    }

    public function show(Request $request, string $page): PageResource
    {
        $this->checkAbilities(ApiAbilitties::page_view->value);

        /** @var Page $page */
        $page = QueryBuilder::for(Page::whereSlug($page))
            ->allowedIncludes([
                'blockContents.block',
                'dataTranslation',
                'parentTranslation',
                'routeUrls',
                'metaData',
            ])
            ->firstOrFail();

        $ignoreQuery = array_diff(array_keys($request->query->all()), ['signature', 'expires']);

        abort_if($page->isPublished() && ! URL::hasValidSignature($request, false, $ignoreQuery), 412);

        return PageResource::make($page);
    }
}
