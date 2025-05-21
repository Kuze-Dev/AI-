<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
class EmptyResource extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        return [];
    }
}
