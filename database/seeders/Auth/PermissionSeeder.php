<?php

declare(strict_types=1);

namespace Database\Seeders\Auth;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public const FILAMENT_ABILITIES = [
        'viewAny',
        'view',
        'create',
        'update',
        'deleteAny',
        'delete',
    ];

    public const FILAMENT_SOFT_DELETES_ABILITIES = [
        'restoreAny',
        'restore',
        'forceDeleteAny',
        'forceDelete',
    ];

    public function run(): void
    {
        collect($this->permissionsByGuard())
            ->map(fn (array $permissions) => collect($permissions))
            ->each(
                fn (Collection $permissions, string $guard) => $permissions->each(
                    fn (string $permission) => Permission::create([
                        'name' => $permission,
                        'guard_name' => $guard,
                    ])
                )
            );
    }

    protected function permissionsByGuard(): array
    {
        return [
            'admin' => [
                ...$this->generateFilamentResourcePermissions(
                    'admin',
                    except: [
                        'deleteAny',
                        'restoreAny',
                        'forceDeleteAny',
                    ],
                    hasSoftDeletes: true,
                    customPermissions: [
                        'resendVerification',
                        'sendPasswordReset',
                    ]
                ),
                ...$this->generateFilamentResourcePermissions('role', except: ['deleteAny']),
            ],
        ];
    }

    protected function generateFilamentResourcePermissions(
        string $resourceName,
        array $only = [],
        array $except = [],
        bool $hasSoftDeletes = false,
        array $customPermissions = [],
    ): array {
        return collect(self::FILAMENT_ABILITIES)
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
            ->map(fn (string $ability) => "{$resourceName}.{$ability}")
            ->prepend($resourceName)
            ->toArray();
    }
}
