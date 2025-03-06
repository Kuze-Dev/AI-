<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use App\HttpTenantApi\Resources\Concerns\TransformsSchemaPayload;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Globals\Models\Globals
 */
class GlobalsResource extends JsonApiResource
{
    use TransformsSchemaPayload;

    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'data' => $this->transformSchemaPayload($this->data ?? []),
        ];
    }


    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'blueprint' => fn () => BlueprintResource::make($this->blueprint),
            'blueprintData' => fn () => BlueprintDataResource::collection($this->blueprintData),
            'dataTranslation' => fn () => self::collection($this->dataTranslation),
            'parentTranslation' => fn () => self::make($this->parentTranslation),
        ];
    }

    protected function getSchemaData(): SchemaData
    {
        return $this->blueprint->schema;
    }
}
