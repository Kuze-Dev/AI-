<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Support\RouteUrl\Models\RouteUrl
 */
class RouteUrlResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'url' => $this->url,
            'is_override' => $this->is_override,
        ];
    }
}
