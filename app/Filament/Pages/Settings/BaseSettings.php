<?php

declare(strict_types=1);

namespace App\Filament\Pages\Settings;

use App\Filament\Resources\RoleResource\Support\PermissionGroup;
use Filament\Pages\SettingsPage;
use Illuminate\Support\Facades\Route;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

abstract class BaseSettings extends SettingsPage
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $breadcrumb = null;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    public function mount(): void
    {
        abort_unless(self::authorizeAccess(), 403);
    }

    public static function shouldShowSettingsCard(): bool
    {
        return self::authorizeAccess();
    }

    protected static function authorizeAccess(): bool
    {
        $settingsPermissions = app(PermissionRegistrar::class)
            ->getPermissions()
            ->filter(fn (Permission $permission) => Str::startsWith($permission->name, 'settings'));

        if ($settingsPermissions->isEmpty()) {
            return true;
        }

        if (PermissionGroup::make($settingsPermissions)->getParts()->contains(self::getSlug())) {
            return true;
        }

        return Auth::user()?->can('settings.' . self::getSlug()) ?? false;
    }

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
