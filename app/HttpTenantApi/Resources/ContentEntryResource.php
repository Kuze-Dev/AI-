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
 * @mixin \Domain\Content\Models\ContentEntry
 */
class ContentEntryResource extends JsonApiResource
{
    use TransformsSchemaPayload;

    public function toAttributes(Request $request): array
    {
        return [
            'title' => $this->title,
            'data' => $this->transformSchemaPayload($this->data),
            'order' => $this->order,
            'published_at' => $this->published_at,
            'route_url' => $this->qualified_route_url,
        ];
    }

    /** @return array<string, callable> */
    public function toRelationships(Request $request): array
    {
        return [
            'taxonomyTerms' => fn () => TaxonomyTermResource::collection($this->taxonomyTerms),
            'slugHistories' => fn () => SlugHistoryResource::collection($this->slugHistories),
            'metaData' => fn () => MetaDataResource::make($this->metaData),
        ];
    }

    protected function getSchemaData(): SchemaData
    {
        return $this->content->blueprint->schema;
    }

    public static function newCollection(mixed $resource)
    {
        if ($resource instanceof Collection) {
            $resource->loadMissing('content.blueprint');
        }

        if ($resource instanceof LengthAwarePaginator && $resource->getCollection() instanceof Collection) {
            $resource->getCollection()->loadMissing('content.blueprint');
        }

        return parent::newCollection($resource);
    }
}
