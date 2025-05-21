<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use App\HttpTenantApi\Resources\Concerns\TransformsSchemaPayload;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use TiMacDonald\JsonApi\JsonApiResource;
use TiMacDonald\JsonApi\JsonApiResourceCollection;

/**
 * @mixin \Domain\Page\Models\BlockContent
 *
 * @property-read \Domain\Page\Models\Block $block
 * @property-read \Domain\Blueprint\Models\Blueprint $blueprint
 */
class BlockContentResource extends JsonApiResource
{
    use TransformsSchemaPayload;

    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'data' => $this->transformSchemaPayload($this->data ?? []),
            'order' => $this->order,
            'blueprintData' => fn () => BlueprintDataResource::collection($this->blueprintData),
        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'block' => fn () => BlockResource::make($this->block),
            'blueprintData' => fn () => BlueprintDataResource::collection($this->blueprintData),
        ];
    }

    protected function getSchemaData(): SchemaData
    {
        return $this->block->blueprint->schema;
    }

    #[\Override]
    public static function newCollection(mixed $resource): JsonApiResourceCollection
    {
        if ($resource instanceof Collection) {
            $resource->loadMissing('block.blueprint');
        }

        if ($resource instanceof LengthAwarePaginator && $resource->getCollection() instanceof Collection) {
            $resource->getCollection()->loadMissing('block.blueprint');
        }

        return parent::newCollection($resource);
    }
}
