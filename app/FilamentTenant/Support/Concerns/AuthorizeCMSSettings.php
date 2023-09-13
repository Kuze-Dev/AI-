<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support\Concerns;

use App\Features\ECommerce\ECommerceBase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Filament\Resources\RoleResource\Support\PermissionGroup;

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

        if (!PermissionGroup::make($settingsPermissions)->getParts()->contains(self::getSlug())) {
            return true;
        }

        /** @var \Domain\Admin\Models\Admin $user */
        $user = auth()->user();

        return $user?->can('cmsSettings.' . self::getSlug()) ?? false;
    }
}
