<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings;
use App\Filament\Pages\Concerns\LogsFormActivity;
use App\Filament\Resources\RoleResource\Support\PermissionGroup;
use Filament\Pages\SettingsPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

abstract class BaseSettings extends SettingsPage
{
    use LogsFormActivity;

    protected static ?string $cluster = Settings::class;

    public static function canAccess(): bool
    {
        $settingsPermissions = app(PermissionRegistrar::class)
            ->getPermissions()
            ->filter(fn (Permission $permission) => Str::startsWith($permission->name, 'settings'));

        if ($settingsPermissions->isEmpty()) {
            return true;
        }

        if (! PermissionGroup::make($settingsPermissions)->getParts()->contains(self::getSlug())) {
            return true;
        }

        return Auth::user()?->can('settings.'.self::getSlug()) ?? false;
    }
}
