<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Collection\Models\CollectionEntry
 */
class CollectionEntryResource extends JsonApiResource
{
    public function toAttributes($request): array
    {
        return [
            'title' => $this->title,
            'data' => $this->data,
            'order' => $this->order,
            'published_at' => $this->published_at,
        ];
    }

    public function toRelationships($request): array
    {
        return [
            'taxonomyTerms' => fn () => TaxonomyTermResource::collection($this->taxonomyTerms),
            'slugHistories' => fn () => SlugHistoryResource::collection($this->slugHistories),
        ];
    }
}
