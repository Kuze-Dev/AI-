<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Settings;

use App\HttpTenantApi\Resources\SettingResource;
use Spatie\LaravelSettings\SettingsContainer;
use Spatie\RouteAttributes\Attributes\Get;
use TiMacDonald\JsonApi\JsonApiResource;

class SettingController
{
    #[Get('/settings/{group}')]
    public function __invoke(string $group, SettingsContainer $settingsContainer): JsonApiResource
    {
        /** @var class-string<\Spatie\LaravelSettings\Settings>|null */
        $settingClass = $settingsContainer->getSettingClasses()
            ->first(fn (string $settingsClass) => $settingsClass::group() === $group);

        if ($settingClass === null) {
            abort(404);
        }

        return SettingResource::make(app($settingClass));
    }
}
