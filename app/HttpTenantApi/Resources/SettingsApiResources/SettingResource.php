<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources\SettingsApiResources;

use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read string $name
 * @property-read string $description
 * @property-read string $author
 * @property-read string $logo
 * @property-read string $favicon
 */
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
