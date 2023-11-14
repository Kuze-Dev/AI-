<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use App\Features;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Tenant\Models\Tenant
 */
class TenantSpecsResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
        ];
    }

}
