<?php

declare(strict_types=1);

namespace App\HttpApi\Resources;

use Illuminate\Http\Request;
use Spatie\LaravelSettings\Settings;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @property-read Settings $resource
 */
class SettingsResource extends JsonApiResource
{
    public function toId(Request $request): string
    {
        return $this->resource::group();
    }

    public function toType(Request $request): string
    {
        return 'settings';
    }

    public function toAttributes(Request $request): array
    {
        return $this->resource->toArray();
    }
}
