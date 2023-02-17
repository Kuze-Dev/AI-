<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Globals\Models\Globals
 */
class GlobalsResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return  [
            'name' => $this->name,
            'slug' => $this->slug,
            'data' => $this->data,
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'slugHistories' => fn () => SlugHistoryResource::collection($this->slugHistories),
        ];
    }
}
