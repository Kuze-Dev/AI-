<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use App\HttpTenantApi\Resources\Concerns\TransformsSchemaPayload;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Content\Models\ContentEntry
 */
class ContentEntryResource extends JsonApiResource
{
    use TransformsSchemaPayload;

    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'title' => $this->title,
            'data' => $this->transformSchemaPayload($this->data),
            'order' => $this->order,
            'published_at' => $this->published_at,
            'route_url' => $this->activeRouteUrl?->url,
            'locale' => $this->locale,

            // TODO: remove this
            'blueprintData' => fn () => BlueprintDataResource::collection($this->blueprintData),
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'taxonomyTerms' => fn () => TaxonomyTermResource::collection($this->taxonomyTerms),
            'routeUrls' => fn () => RouteUrlResource::make($this->routeUrls),
            'metaData' => fn () => MetaDataResource::make($this->metaData),
            'content' => fn () => ContentResource::make($this->content),
            'blueprintData' => fn () => BlueprintDataResource::collection($this->blueprintData),
        ];
    }

    protected function getSchemaData(): SchemaData
    {
        return $this->content->blueprint->schema;
    }

    #[\Override]
    public static function newCollection(mixed $resource)
    {
        if ($resource instanceof Collection) {
            $resource->loadMissing('content.blueprint', 'activeRouteUrl');
        }

        if ($resource instanceof LengthAwarePaginator && $resource->getCollection() instanceof Collection) {
            $resource->getCollection()->loadMissing('content.blueprint', 'activeRouteUrl');
        }

        return parent::newCollection($resource);
    }
}
