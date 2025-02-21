<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support\Concerns;

use App\Filament\Resources\RoleResource\Support\PermissionGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

trait AuthorizeCMSSettings
{
    protected static function authorizeAccess(): bool
    {
        $settingsPermissions = app(PermissionRegistrar::class)
            ->getPermissions()
            ->filter(fn (Permission $permission) => Str::startsWith($permission->name, 'cmsSettings'));

        if ($settingsPermissions->isEmpty()) {
            return true;
        }

        if (! PermissionGroup::make($settingsPermissions)->getParts()->contains(self::getSlug())) {
            return true;
        }

        return filament_admin()->can('cmsSettings.'.self::getSlug()) ?? false;
    }
}
