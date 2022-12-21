<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read string $name
 * @property-read \Domain\Blueprint\Models\Blueprint $blueprint
 */
class FormResource extends JsonApiResource
{
    protected function toAttributes(Request $request): array
    {
        return  [
            'name' => $this->name,
        ];
    }

    protected function toRelationships(Request $request): array
    {
        return [
            'blueprint' => fn () => BlueprintResource::make($this->blueprint),
        ];
    }
}
