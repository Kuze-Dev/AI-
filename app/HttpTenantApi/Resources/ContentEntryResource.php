<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use App\HttpTenantApi\Resources\Concerns\TransformsSchemaPayload;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Content\Models\ContentEntry
 */
class ContentEntryResource extends JsonApiResource
{
    use TransformsSchemaPayload;

    public function toAttributes(Request $request): array
    {
        return [
            'title' => $this->title,
            'data' => $this->transformSchemaPayload($this->data),
            'order' => $this->order,
            'published_at' => $this->published_at,
            'route_url' => $this->activeRouteUrl->url,
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'taxonomyTerms' => fn () => TaxonomyTermResource::collection($this->taxonomyTerms),
            'routeUrls' => fn () => RouteUrlResource::collection($this->routeUrls),
            'metaData' => fn () => MetaDataResource::make($this->metaData),
        ];
    }

    protected function getSchemaData(): SchemaData
    {
        return $this->content->blueprint->schema;
    }
}
