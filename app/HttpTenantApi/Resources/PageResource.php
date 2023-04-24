<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Page\Models\Page
 */
class PageResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return  [
            'name' => $this->name,
            'route_url' => $this->qualified_route_url,
            'published_at' => $this->published_at,
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'blockContents' => fn () => BlockContentResource::collection($this->blockContents),
            'slugHistories' => fn () => SlugHistoryResource::collection($this->slugHistories),
            'metaData' => fn () => MetaDataResource::make($this->metaData),
        ];
    }
}
