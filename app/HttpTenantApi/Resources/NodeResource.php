<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read string $label
 * @property-read string $url
 * @property-read int $sort
 * @property-read string $target
 * @property-read array $childs
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
