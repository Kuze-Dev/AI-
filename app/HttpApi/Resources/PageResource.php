<?php

declare(strict_types=1);

namespace App\HttpApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read string $name
 */
class PageResource extends JsonApiResource
{
    protected function toAttributes(Request $request): array
    {
        return  [
            'name' => $this->name,
        ];
    }
}
