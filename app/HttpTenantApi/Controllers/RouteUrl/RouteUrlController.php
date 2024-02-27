<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\RouteUrl;

use App\Features\CMS\CMSBase;
use App\Features\CMS\SitesManagement;
use App\HttpTenantApi\Resources\ContentEntryResource;
use App\HttpTenantApi\Resources\PageResource;
use Domain\Content\Models\ContentEntry;
use Domain\Page\Models\Page;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Where;
use Support\RouteUrl\Models\RouteUrl;
use TiMacDonald\JsonApi\JsonApiResource;

#[Middleware('feature.tenant:'.CMSBase::class)]
class RouteUrlController
{
    #[
        Get('/route/{url?}'),
        Where('url', '.*')
    ]
    public function __invoke(string $url = ''): JsonApiResource
    {
        $queryRouteUrl = RouteUrl::whereUrl(Str::start($url, '/'))
            ->with('model');

        if (
            TenantFeatureSupport::active(SitesManagement::class) &&
            request('site')
        ) {

            $siteId = request('site');

            $queryRouteUrl->whereHas('model', function ($query) use ($siteId) {
                return $query->whereHas('sites', fn ($q) => $q->where('site_id', $siteId));
            });

        }

        $queryRouteUrl->whereHas('model', function ($query) {
            return $query->where('draftable_id', null);
        });

        $routeUrl = $queryRouteUrl->firstOrFail();

        return match ($routeUrl->model::class) {
            Page::class => PageResource::make($routeUrl->model),
            ContentEntry::class => ContentEntryResource::make($routeUrl->model),
            default => throw new InvalidArgumentException('No resource found for model '.$routeUrl->model::class),
        };
    }
}
