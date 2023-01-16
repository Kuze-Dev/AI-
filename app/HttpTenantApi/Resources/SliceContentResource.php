<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Page\Models\SliceContent
 */
class SliceContentResource extends JsonApiResource
{
    protected function toAttributes(Request $request): array
    {
        return  [
            'data' => $this->data,
        ];
    }

    protected function toRelationships(Request $request): array
    {
        return [
            'slice' => fn () => SliceResource::make($this->slice),
        ];
    }
}
