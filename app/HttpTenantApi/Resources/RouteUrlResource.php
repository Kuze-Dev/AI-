<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Page\Models\Page;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Support\RouteUrl\Models\RouteUrl
 */
class RouteUrlResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return  [
            'url' => $this->url,
            'is_override' => $this->is_override,
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            /** @phpstan-ignore-next-line  */
            'model' => fn () => match ($this->model::class) {
                Page::class => PageResource::make($this->model),
                Content::class => ContentResource::make($this->model),
                ContentEntry::class => ContentEntryResource::make($this->model),
            },
        ];
    }
}
