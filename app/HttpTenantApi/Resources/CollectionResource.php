<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read string $name,
 * @property-read string|null $past_publish_date_behavior
 * @property-read string|null $future_publish_date_behavior
 * @property-read bool $is_sortable
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
            'blueprint' => fn () => BlueprintResource::make($this->blueprint),
            'taxonomies' => fn () => TaxonomyResource::collection($this->taxonomies),
            'collectionEntries' => fn () => CollectionEntryResource::collection($this->collectionEntries)
        ];
    }
}
