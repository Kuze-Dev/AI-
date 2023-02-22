<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Menu\Models\Node
 */
class NodeResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return  [
            'label' => $this->label,
            'url' => $this->url,
            'target' => $this->target,
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'children' => fn () => NodeResource::collection($this->children),
        ];
    }
}
