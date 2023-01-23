<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read string $name
 * @property-read \Domain\Menu\Models\Node $nodes
 */
class MenuResource extends JsonApiResource
{
    public function toAttributes($request): array
    {
        return  [
            'name' => $this->name,
            'nodes' => $this->nodes,
        ];
    }
}
