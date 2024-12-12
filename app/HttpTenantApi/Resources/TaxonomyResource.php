<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Taxonomy\Models\Taxonomy
 */
class TaxonomyResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'route_url' => $this->activeRouteUrl?->url,
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'taxonomyTerms' => fn () => TaxonomyTermResource::collection($this->taxonomyTerms),
            'parentTerms' => fn () => TaxonomyTermResource::collection($this->parentTerms),
            'dataTranslation' => fn () => self::collection($this->dataTranslation),
            'parentTranslation' => fn () => self::make($this->parentTranslation),
        ];
    }
}
