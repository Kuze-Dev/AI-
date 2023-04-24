<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Page\Models\Page
 */
class PageResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return  [
            'name' => $this->name,
            'route_url' => $this->activeRouteUrl->url,
            'published_at' => $this->published_at,
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'blockContents' => fn () => BlockContentResource::collection($this->blockContents),
            'routeUrls' => fn () => RouteUrlResource::collection($this->routeUrls),
            'metaData' => fn () => MetaDataResource::make($this->metaData),
        ];
    }
}
