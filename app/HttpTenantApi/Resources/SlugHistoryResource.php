<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\Support\SlugHistory\SlugHistory
 */
class SlugHistoryResource extends JsonApiResource
{
    public function toAttributes($request): array
    {
        return  [
            'slug' => $this->slug,
        ];
    }
}
