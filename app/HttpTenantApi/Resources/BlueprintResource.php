<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read string $name
 * @property-read  \Domain\Blueprint\DataTransferObjects\SchemaData $schema
 */
class BlueprintResource extends JsonApiResource
{
    public function toAttributes($request): array
    {
        return  [
            'name' => $this->name,
            'schema' => $this->schema,
        ];
    }
}
