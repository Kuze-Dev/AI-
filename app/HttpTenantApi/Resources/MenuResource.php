<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Menu\Models\Menu
 */
class MenuResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'locale' => $this->locale,
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'nodes' => fn () => NodeResource::collection($this->nodes),
            'parentNodes' => fn () => NodeResource::collection($this->parentNodes),
            'dataTranslation' => fn () => self::collection($this->dataTranslation),
            'parentTranslation' => fn () => self::make($this->parentTranslation),
        ];
    }
}
