<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;


use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Blueprint\Models\BlueprintData
 */
class BlueprintDataResource extends JsonApiResource
{
    public function toRelationships(Request $request): array
    {
        return [
            'media' => fn () => MediaResource::make($this->media),
        ];
    }


}
