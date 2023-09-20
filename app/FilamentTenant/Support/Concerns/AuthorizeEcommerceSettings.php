<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support\Concerns;

use App\Features\ECommerce\ECommerceBase;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Filament\Resources\RoleResource\Support\PermissionGroup;
use Illuminate\Support\Facades\Auth;

trait AuthorizeEcommerceSettings
{
    protected static function authorizeAccess(): bool
    {
        if (tenancy()->tenant?->features()->inactive(ECommerceBase::class)) {
            return false;
        }

        $settingsPermissions = app(PermissionRegistrar::class)
            ->getPermissions()
            ->filter(fn (Permission $permission) => Str::startsWith($permission->name, 'ecommerceSettings'));

        if ($settingsPermissions->isEmpty()) {
            return true;
        }

        if ( ! PermissionGroup::make($settingsPermissions)->getParts()->contains(self::getSlug())) {
            return true;
        }

        return Auth::user()?->can('ecommerceSettings.' . self::getSlug()) ?? false;
    }
}
