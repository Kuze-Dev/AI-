<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\RouteUrl;

use App\Features\CMS\CMSBase;
use App\Features\CMS\SitesManagement;
use App\HttpTenantApi\Resources\ContentEntryResource;
use App\HttpTenantApi\Resources\PageResource;
use App\HttpTenantApi\Resources\TaxonomyResource;
use App\HttpTenantApi\Resources\TaxonomyTermResource;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Page\Enums\Visibility;
use Domain\Page\Models\Page;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
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
        $notDraftableModels = [
            app(Taxonomy::class)->getMorphClass(),
            app(TaxonomyTerm::class)->getMorphClass(),
        ];

        /** @var \Illuminate\Database\Eloquent\Builder<RouteUrl> $queryRouteUrl */
        $queryRouteUrl = RouteUrl::whereUrl(Str::start($url, '/'))
            ->with('model');

        if (
            TenantFeatureSupport::active(SitesManagement::class) &&
            request('site')
        ) {

            $siteId = request('site');

            $queryRouteUrl->whereHas('model', function ($query) use ($siteId) {

                if ($query->getModel()->getMorphClass() == app(TaxonomyTerm::class)->getMorphClass()) {

                    return $query->whereHas('taxonomy', fn($parentQuery) => $parentQuery->whereHas('sites', fn ($q) => $q->where('site_id', $siteId)));
                    // fn ($q) => $q->where('site_id', $siteId));
                }

                return $query->whereHas('sites', fn ($q) => $q->where('site_id', $siteId));
            });

        }

        $queryRouteUrl->whereHas('model', function ($query) use ($notDraftableModels) {

            if (! in_array($query->getModel()->getMorphClass(), $notDraftableModels)) {

                return $query->where('draftable_id', null);
            }

            return $query;
        });

        $routeUrl = $queryRouteUrl->firstOrFail();

        return match ($routeUrl->model::class) {
            Page::class => PageResource::make($routeUrl->model),
            ContentEntry::class => $this->handleContentEntryResource($routeUrl->model),
            Taxonomy::class => TaxonomyResource::make($routeUrl->model),
            TaxonomyTerm::class => TaxonomyTermResource::make($routeUrl->model),
            default => throw new InvalidArgumentException('No resource found for model '.$routeUrl->model::class),
        };
    }

    private function handleContentEntryResource(ContentEntry $contentEntry): ContentEntryResource
    {
        /** @var \Domain\Content\Models\Content */
        $content = Content::whereId($contentEntry->content_id)->firstOrFail();

        abort_if($content->visibility === Visibility::AUTHENTICATED->value, 403);

        abort_if($contentEntry->status == false, 404);

        return ContentEntryResource::make($contentEntry);
    }
}
