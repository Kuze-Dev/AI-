<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read string $name
 * @property-read  \Domain\Blueprint\DataTransferObjects\SchemaData $schema
 */
class BlueprintResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'schema' => $this->schema,
        ];
    }
}
