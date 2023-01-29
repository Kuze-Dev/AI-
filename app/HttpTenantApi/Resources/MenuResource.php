<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Menu\Models\Menu
 */
class MenuResource extends JsonApiResource
{
    public function toAttributes($request): array
    {
        return  [
            'name' => $this->name,
        ];
    }

    public function toRelationships($request): array
    {
        return [
            'nodes' => fn () => NodeResource::collection($this->nodes),
        ];
    }
}
