<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use App\HttpTenantApi\Resources\Concerns\TransformsSchemaPayload;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Collection\Models\CollectionEntry
 */
class CollectionEntryResource extends JsonApiResource
{
    use TransformsSchemaPayload;

    public function toAttributes($request): array
    {
        return [
            'title' => $this->title,
            'data' => $this->transformSchemaPayload($this->data),
            'order' => $this->order,
            'published_at' => $this->published_at,
            'route_url' => $this->qualified_route_url,
        ];
    }

    public function toRelationships($request): array
    {
        return [
            'taxonomyTerms' => fn () => TaxonomyTermResource::collection($this->taxonomyTerms),
            'slugHistories' => fn () => SlugHistoryResource::collection($this->slugHistories),
        ];
    }

    protected function getSchemaData(): SchemaData
    {
        return $this->collection->blueprint->schema;
    }
}
