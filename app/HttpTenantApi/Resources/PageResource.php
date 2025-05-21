<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use TiMacDonald\JsonApi\JsonApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

/**
 * @mixin \Domain\Page\Models\Page
 */
class PageResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'route_url' => $this->activeRouteUrl?->url,
            'visibility' => $this->visibility,
            'published_at' => $this->published_at,
            'locale' => $this->locale,
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'blockContents' => fn () => BlockContentResource::collection($this->blockContents),
            'routeUrls' => fn () => RouteUrlResource::make($this->routeUrls),
            'metaData' => fn () => MetaDataResource::make($this->metaData),
            'dataTranslation' => fn () => self::collection($this->dataTranslation),
            'parentTranslation' => fn () => self::make($this->parentTranslation),
        ];
    }

    #[\Override]
    public static function newCollection(mixed $resource): JsonApiResourceCollection
    {
        if ($resource instanceof Collection) {
            $resource->loadMissing('activeRouteUrl');
        }

        if ($resource instanceof LengthAwarePaginator && $resource->getCollection() instanceof Collection) {
            $resource->getCollection()->loadMissing('activeRouteUrl');
        }

        return parent::newCollection($resource);
    }
}
