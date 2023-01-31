<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Collection\Models\Collection
 */
class CollectionResource extends JsonApiResource
{
    public function toAttributes($request): array
    {
        return [
            'name' => $this->name,
            'past_publish_date_behavior' => $this->past_publish_date_behavior,
            'future_publish_date_behavior' => $this->future_publish_date_behavior,
            'is_sortable' => $this->is_sortable,
        ];
    }

    public function toRelationships($request): array
    {
        return [
            'taxonomies' => fn () => TaxonomyResource::collection($this->taxonomies),
            'slugHistories' => fn () => SlugHistoryResource::collection($this->slugHistories)
        ];
    }
}
