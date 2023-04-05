<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Collection\Models\Collection
 */
class CollectionResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'past_publish_date_behavior' => $this->past_publish_date_behavior,
            'future_publish_date_behavior' => $this->future_publish_date_behavior,
            'is_sortable' => $this->is_sortable,
            'route_url' => $this->qualified_route_url,
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'taxonomies' => fn () => TaxonomyResource::collection($this->taxonomies),
            'routeUrls' => fn () => RouteUrlResource::collection($this->routeUrls),
        ];
    }
}
