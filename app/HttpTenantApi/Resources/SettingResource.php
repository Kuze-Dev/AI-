<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use TiMacDonald\JsonApi\JsonApiResource;


class SettingResource extends JsonApiResource
{
    public function toId($request): string
    {
        return $this->resource::group();
    }

    public function toType($request): string
    {
        return 'settings';
    }

    public function toAttributes($request): array
    {
        return $this->resource->toArray();
    }
}
