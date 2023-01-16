<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Page\Models\Slice
 */
class SliceResource extends JsonApiResource
{
    protected function toAttributes(Request $request): array
    {
        return  [
            'name' => $this->name,
            'component' => $this->component,
        ];
    }
}
