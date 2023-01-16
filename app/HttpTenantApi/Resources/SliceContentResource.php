<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Page\Models\SliceContent
 */
class SliceContentResource extends JsonApiResource
{
    public function toAttributes($request): array
    {
        return  [
            'data' => $this->data,
        ];
    }

    public function toRelationships($request): array
    {
        return [
            'slice' => fn () => SliceResource::make($this->slice),
        ];
    }
}
