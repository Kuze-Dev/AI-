<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read string $name
 * @property-read string|null $description
 * @property-read \Domain\Taxonomy\Models\Taxonomy $taxonomy
 */
class TaxonomyTermResource extends JsonApiResource
{
    public function toAttributes($request): array
    {
        return  [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
