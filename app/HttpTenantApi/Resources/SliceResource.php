<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Page\Models\Slice
 */
class SliceResource extends JsonApiResource
{
    public function toAttributes($request): array
    {
        return  [
            'name' => $this->name,
            'component' => $this->component,
        ];
    }
}
