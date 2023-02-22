<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use App\HttpTenantApi\Resources\Concerns\TransformsSchemaPayload;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Page\Models\SliceContent
 */
class SliceContentResource extends JsonApiResource
{
    use TransformsSchemaPayload;

    public function toAttributes(Request $request): array
    {
        return  [
            'data' => $this->transformSchemaPayload($this->data ?? []),
            'order' => $this->order,
        ];
    }

    public function toRelationships(Request $request): array
    {
        return [
            'slice' => fn () => SliceResource::make($this->slice),
        ];
    }

    protected function getSchemaData(): SchemaData
    {
        return $this->slice->blueprint->schema;
    }

    public static function newCollection(mixed $resource)
    {
        if ($resource instanceof Collection) {
            $resource->loadMissing('slice.blueprint');
        }

        if ($resource instanceof LengthAwarePaginator && $resource->getCollection() instanceof Collection) {
            $resource->getCollection()->loadMissing('slice.blueprint');
        }

        return parent::newCollection($resource);
    }
}
