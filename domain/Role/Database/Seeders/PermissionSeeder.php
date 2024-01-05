<?php

declare(strict_types=1);

namespace Domain\Role\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

abstract class PermissionSeeder extends Seeder
{
    final public const FILAMENT_ABILITIES = [
        'viewAny',
        'view',
        'create',
        'update',
        'deleteAny',
        'delete',
    ];

    final public const FILAMENT_SOFT_DELETES_ABILITIES = [
        'restoreAny',
        'restore',
        'forceDeleteAny',
        'forceDelete',
    ];

    public function run(): void
    {
        collect($this->permissionsByGuard())
            ->map(fn (array $permissions) => collect($permissions))
            ->each(function (Collection $permissions, string $guard) {
                $permissions->each(fn (string $permission) => Permission::updateOrCreate([
                    'name' => $permission,
                    'guard_name' => $guard,
                ]));

                Permission::whereGuardName($guard)->whereNotIn('name', $permissions)->delete();
            });

        $this->command->callSilently('permission:cache-reset');
    }

    abstract protected function permissionsByGuard(): array;

    protected function generateFilamentResourcePermissions(
        string $resourceName,
        array $only = [],
        array $except = [],
        bool $hasSoftDeletes = false,
        array $customPermissions = [],
    ): array {
        $permissions = collect(self::FILAMENT_ABILITIES)
            ->when(
                $hasSoftDeletes,
                fn (Collection $abilities) => $abilities->merge(self::FILAMENT_SOFT_DELETES_ABILITIES)
            )
            ->when(
                count($only),
                fn (Collection $abilities) => $abilities->intersect($only)
            )
            ->when(
                count($except),
                fn (Collection $abilities) => $abilities->diff($except)
            )
            ->merge($customPermissions)
            ->toArray();

        return $this->generatePermissionGroup($resourceName, $permissions);
    }

    protected function generatePermissionGroup(string $resourceName, array $permissions): array
    {
        return collect($permissions)
            ->map(fn (string $permission) => "{$resourceName}.{$permission}")
            ->prepend($resourceName)
            ->toArray();
    }
}
