<?php

declare(strict_types=1);

namespace App\FilamentTenant\Pages\Settings;

use App\Filament\Pages\Settings\BaseSettings;
use Filament\Facades\Filament;

abstract class TenantBaseSettings extends BaseSettings
{
    public static function getRouteName(?string $panel = null): string
    {
        return Filament::currentContext().'.pages.settings.'.static::getSlug();
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumb = $this->getBreadcrumb();

        return array_merge(
            [Settings::getUrl() => trans('Settings')],
            (filled($breadcrumb) ? [$breadcrumb] : [])
        );
    }
}
