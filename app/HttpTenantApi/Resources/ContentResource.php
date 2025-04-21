<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Content\Models\Content
 */
class ContentResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'past_publish_date_behavior' => $this->past_publish_date_behavior,
            'future_publish_date_behavior' => $this->future_publish_date_behavior,
            'is_sortable' => $this->is_sortable,
            'prefix' => $this->prefix,
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'taxonomies' => fn () => TaxonomyResource::collection($this->taxonomies),
        ];
    }
}
