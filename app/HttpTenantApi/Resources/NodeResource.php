<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Menu\Models\Node
 */
class NodeResource extends JsonApiResource
{
    public function toAttributes($request): array
    {
        return  [
            'label' => $this->label,
            'url' => $this->url,
            'target' => $this->target,
        ];
    }

    public function toRelationships($request): array
    {
        return [
            'children' => fn () => NodeResource::collection($this->children),
        ];
    }
}
