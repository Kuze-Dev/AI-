<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use App\HttpTenantApi\Resources\Concerns\TransformsSchemaPayload;
use Illuminate\Http\Request;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Globals\Models\Globals
 */
class GlobalsResource extends JsonApiResource
{
    use TransformsSchemaPayload;

    public function toAttributes(Request $request): array
    {
        return  [
            'name' => $this->name,
            'slug' => $this->slug,
            'data' => $this->transformSchemaPayload($this->data ?? []),
        ];
    }

    protected function getSchemaData(): SchemaData
    {
        return $this->blueprint->schema;
    }
}
