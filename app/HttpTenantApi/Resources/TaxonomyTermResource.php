<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read string $name
 * @property-read string|null $description
 * @property-read \Domain\Taxonomy\Models\Taxonomy $taxonomy
 */
class TaxonomyTermResource extends JsonApiResource
{
    protected function toAttributes(Request $request): array
    {
        return  [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
