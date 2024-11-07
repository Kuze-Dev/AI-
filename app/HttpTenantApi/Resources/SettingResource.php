<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/** @property-read \Spatie\LaravelSettings\Settings $resource */
class SettingResource extends JsonApiResource
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
        return $this->resource
            ->toCollection()
            ->except(
                array_merge($this->resource::encrypted(),
                    [
                        'deploy_hook',
                    ]
                ))
            ->toArray();
    }
}
