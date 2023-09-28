<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Service\Models\Service;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin Service
 */
class ServiceResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'data' => $this->data,
            'is_featured' => $this->is_featured,
            'is_special_offer' => $this->is_special_offer,
            'is_subscription' => $this->is_subscription,
            'status' => $this->status,
        ];
    }

    /**
     * @param Request $request
     * @return array<string, callable>
     */
    public function toRelationships(Request $request): array
    {
        return [
            'media' => fn () => MediaResource::collection($this->media),
            'taxonomyTerms' => fn () => TaxonomyTermResource::collection($this->taxonomyTerms),
            'metaData' => fn () => MetaDataResource::make($this->metaData),
        ];
    }
}
