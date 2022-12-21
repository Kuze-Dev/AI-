<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
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
    protected function toAttributes(Request $request): array
    {
        return  [
            'label' => $this->label,
            'url' => $this->url,
            'target' => $this->target,
            'sort' => $this->sort,
            'childs' => $this->childs,
        ];
    }

    protected function toRelationships(Request $request): array
    {
        return [
            'childs' => fn () => NodeResource::make($this->childs),
        ];
    }
}
