<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use App\HttpTenantApi\Resources\Concerns\TransformsSchemaPayload;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Illuminate\Http\Request;
use RuntimeException;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Taxonomy\Models\TaxonomyTerm
 */
class TaxonomyTermResource extends JsonApiResource
{
    use TransformsSchemaPayload;

    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'route_url' => $this->activeRouteUrl?->url,
            'data' => $this->transformSchemaPayload($this->data),
            'order' => $this->order,
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'children' => fn () => TaxonomyTermResource::collection($this->children),
            'blueprintData' => fn () => BlueprintDataResource::collection($this->blueprintData),
            'taxonomy' => fn () => TaxonomyResource::make($this->taxonomy),
            'dataTranslation' => fn () => self::collection($this->dataTranslation),
            'parentTranslation' => fn () => self::make($this->parentTranslation),

        ];
    }

    protected function getSchemaData(): SchemaData
    {
        if ($this->taxonomy && $this->taxonomy->blueprint->exists()) {
            return $this->taxonomy->blueprint->schema;
        }

        // Handle the case when taxonomy or blueprint is null
        // Return an appropriate default or throw an exception
        // For example:
        throw new RuntimeException('Invalid taxonomy or missing blueprint.');
    }
}
