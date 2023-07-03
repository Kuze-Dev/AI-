<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Address\Models\State
 */
class StateResource extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
