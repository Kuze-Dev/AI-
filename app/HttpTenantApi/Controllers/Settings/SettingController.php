<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Settings;

use App\HttpTenantApi\Resources\SettingsApiResources\SiteSettingResource;
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
            $resource = match ($group) {
                #add here new settings resource
                'site' => SiteSettingResource::make(app($settingsClass)),

                default => null,
            };

            if ($resource) {
                break;
            }
        }

        if ( ! $resource) {
            abort(404);
        }

        return $resource;
    }
}
