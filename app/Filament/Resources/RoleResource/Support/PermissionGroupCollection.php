<?php

declare(strict_types=1);

namespace App\Filament\Resources\RoleResource\Support;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * @method \Illuminate\Support\Collection<string> keys()
 *
 * @extends \Illuminate\Support\Collection<string, \App\Filament\Resources\RoleResource\Support\PermissionGroup>
 */
class PermissionGroupCollection extends Collection
{
    /**  @param  array<string, mixed>  $params */
    #[\Override]
    public static function make($params = []): self
    {
        $items = app(PermissionRegistrar::class)
            ->getPermissions($params)
            ->sortKeys()
            ->groupBy(fn (Permission $permission, int $key): string => explode('.', $permission->name, 2)[0])
            ->map(fn (Collection $permissionGroup) => PermissionGroup::make($permissionGroup));

        return new self($items);
    }
}
