<?php

declare(strict_types=1);

namespace Domain\Admin\Actions;

use Domain\Admin\DataTransferObjects\RoleData;
use Domain\Admin\Exceptions\CantModifySuperAdminRoleException;
use Spatie\Permission\Models\Role;

class UpdateRoleAction
{
    public function execute(Role $role, RoleData $roleData): Role
    {
        if ($role->name === config('domain.admin.role.super_admin')) {
            throw new CantModifySuperAdminRoleException();
        }

        $role->update(array_filter([
            'name' => $roleData->name,
            'guard_name' => $roleData->guard_name,
        ]));

        $role->syncPermissions($roleData->permissions);

        return $role;
    }
}
