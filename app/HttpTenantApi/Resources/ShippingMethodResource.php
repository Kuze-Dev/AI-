<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\ShippingMethod\Models\ShippingMethod
 */
class ShippingMethodResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {

        return [
            'name' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'driver' => $this->driver,
            'status' => $this->active,

        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'media' => fn () => MediaResource::collection($this->media),
        ];
    }
}
