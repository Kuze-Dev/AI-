<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Page\Models\Page
 */
class PageResource extends JsonApiResource
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
            'sliceContents' => fn () => SliceContentResource::collection($this->sliceContents),
            'slugHistories' => fn () => SlugHistoryResource::collection($this->slugHistories),
        ];
    }
}
