<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Settings;

use App\HttpTenantApi\Resources\SettingsApiResources\SettingResource;
use Spatie\LaravelSettings\SettingsContainer;
use Spatie\RouteAttributes\Attributes\Get;
use TiMacDonald\JsonApi\JsonApiResource;

class SettingController
{
    #[Get('/settings/{group}')]
    public function __invoke(string $group, SettingsContainer $settingsContainer): JsonApiResource
    {
        $resource = null;

        foreach ($settingsContainer->getSettingClasses() as $settingsClass) {
            ($settingsClass::group() == $group) ?
                $resource = SettingResource::make(app($settingsClass)) : null;
        }

        if ( ! $resource) {
            abort(404);
        }

        return $resource;
    }
}
