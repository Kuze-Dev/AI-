<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use Filament\Pages\SettingsPage;
use Illuminate\Support\Facades\Route;
use Closure;

abstract class BaseSettings extends SettingsPage
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $breadcrumb = null;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    public static function getSlug(): string
    {
        return static::$slug ?? static::getSettings()::group();
    }

    public static function getRouteName(): string
    {
        return 'filament.pages.settings.'.self::getSlug();
    }

    protected function getBreadcrumb(): string
    {
        return static::$breadcrumb ?? static::getTitle();
    }

    protected function getBreadcrumbs(): array
    {
        $breadcrumb = $this->getBreadcrumb();

        return array_merge(
            [Settings::getUrl() => trans('Settings')],
            (filled($breadcrumb) ? [$breadcrumb] : [])
        );
    }

    public static function getRoutes(): Closure
    {
        return function () {
            $slug = self::getSlug();

            Route::get('settings/'.$slug, static::class)
                ->middleware(static::getMiddlewares())
                ->name('settings.'.$slug);
        };
    }
}
