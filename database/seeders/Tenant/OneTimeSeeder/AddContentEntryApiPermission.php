<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\OneTimeSeeder;

use Domain\Role\Database\Seeders\PermissionSeeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class AddContentEntryApiPermission extends PermissionSeeder
{
    #[\Override]
    protected function permissionsByGuard(): array
    {
        return [
            'admin-api' => [
                ...$this->generateFilamentResourcePermissions(
                    'contentEntry',
                    except: ['deleteAny']
                ),
                ...$this->generateFilamentResourcePermissions('taxonomyTerm', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('globals', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('product', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('menu', except: ['deleteAny']),
                ...$this->generateFilamentResourcePermissions('page', except: ['deleteAny']),
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
