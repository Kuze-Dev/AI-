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
class SiteSettingResource extends JsonApiResource
{
    public function toId($request): string
    {
        return $request->group;
    }

    public function toType($request): string
    {
        return 'Settings';
    }

    public function toAttributes($request): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'author' => $this->author,
            'logo' => $this->logo,
            'favicon' => $this->favicon,

        ];
    }
}
