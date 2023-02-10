<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use App\HttpTenantApi\Resources\Concerns\TransformsSchemaPayload;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Page\Models\SliceContent
 */
class SliceContentResource extends JsonApiResource
{
    use TransformsSchemaPayload;

    public function toAttributes($request): array
    {
        return  [
            'data' => $this->transformSchemaPayload( ($this->slice->is_fixed_content ? $this->slice->data : $this->data ) ?? []),
            'order' => $this->order,
        ];
    }

    public function toRelationships($request): array
    {
        return [
            'slice' => fn () => SliceResource::make($this->slice),
        ];
    }

    protected function getSchemaData(): SchemaData
    {
        return $this->slice->blueprint->schema;
    }
}
