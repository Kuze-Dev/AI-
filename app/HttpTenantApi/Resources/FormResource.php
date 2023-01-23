<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read string $name
 * @property-read \Domain\Blueprint\Models\Blueprint $blueprint
 */
class FormResource extends JsonApiResource
{
    public function toAttributes($request): array
    {
        return  [
            'name' => $this->name,
        ];
    }

    public function toRelationships($request): array
    {
        return [
            'blueprint' => fn () => BlueprintResource::make($this->blueprint),
        ];
    }
}
