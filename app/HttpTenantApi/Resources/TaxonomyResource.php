<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read string $name
 */
class TaxonomyResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return  [
            'name' => $this->name,
        ];
    }
}
