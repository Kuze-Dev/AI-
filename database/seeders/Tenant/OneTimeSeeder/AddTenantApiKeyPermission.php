<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\OneTimeSeeder;

use Domain\Role\Database\Seeders\PermissionSeeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class AddTenantApiKeyPermission extends PermissionSeeder
{
    #[\Override]
    protected function permissionsByGuard(): array
    {
        return [
            'admin' => [
                ...$this->generateFilamentResourcePermissions('tenantApiKey', except: ['deleteAny'], hasSoftDeletes: true),
            ],
        ];
    }

    public function run(): void
    {
        collect($this->permissionsByGuard())
            ->map(fn (array $permissions) => collect($permissions))
            ->each(function (Collection $permissions, string $guard) {
                $permissions->each(fn (string $permission) => Permission::updateOrCreate([
                    'name' => $permission,
                    'guard_name' => $guard,
                ]));
            });
    }
}
