<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\RouteUrl;

use App\Features\CMS\CMSBase;
use App\HttpTenantApi\Resources\ContentEntryResource;
use App\HttpTenantApi\Resources\PageResource;
use Domain\Content\Models\ContentEntry;
use Domain\Page\Models\Page;
use Support\RouteUrl\Models\RouteUrl;
use Illuminate\Support\Str;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Where;
use TiMacDonald\JsonApi\JsonApiResource;
use InvalidArgumentException;
use Spatie\RouteAttributes\Attributes\Middleware;

#[Middleware('feature.tenant:'. CMSBase::class)]
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

        if(
            tenancy()->tenant?->features()->active(\App\Features\CMS\SitesManagement::class) &&
            request('site')
        ) {

            $siteId = request('site');

            $queryRouteUrl->whereHas('model', function ($query) use ($siteId) {
                return $query->whereHas('sites', fn ($q) => $q->where('site_id', $siteId));
            });

        }

        $routeUrl = $queryRouteUrl->firstOrFail();

        return match ($routeUrl->model::class) {
            Page::class => PageResource::make($routeUrl->model),
            ContentEntry::class => ContentEntryResource::make($routeUrl->model),
            default => throw new InvalidArgumentException('No resource found for model '.$routeUrl->model::class),
        };
    }
}
