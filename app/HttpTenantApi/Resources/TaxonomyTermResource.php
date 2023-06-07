<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use App\HttpTenantApi\Resources\Concerns\TransformsSchemaPayload;
use Domain\Blueprint\DataTransferObjects\SchemaData;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Taxonomy\Models\TaxonomyTerm
 */
class TaxonomyTermResource extends JsonApiResource
{
    use TransformsSchemaPayload;

    public function toAttributes(Request $request): array
    {
        return  [
            'name' => $this->name,
            'data' => $this->transformSchemaPayload($this->data),
            'order' => $this->order,
        ];
    }

       /** @return array<string, callable> */
       public function toRelationships(Request $request): array
       {
           return [
               'children' => fn () => TaxonomyTermResource::collection($this->children),
           ];
       }

    protected function getSchemaData(): SchemaData
    {
        return $this->taxonomy->blueprint->schema;
    }
}
