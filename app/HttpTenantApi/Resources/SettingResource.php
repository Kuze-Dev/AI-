<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/** @property-read \Spatie\LaravelSettings\Settings $resource */
class SettingResource extends JsonApiResource
{
    #[\Override]
    public function toId(Request $request): string
    {
        return $this->resource::group();
    }

    #[\Override]
    public function toType(Request $request): string
    {
        return 'settings';
    }

    #[\Override]
    public function toAttributes(Request $request): array
    {
        return $this->resource
            ->toCollection()
            ->except($this->resource::encrypted())
            ->toArray();
    }
}
