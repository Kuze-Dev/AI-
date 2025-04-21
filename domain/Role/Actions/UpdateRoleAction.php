<?php

declare(strict_types=1);

namespace Domain\Role\Actions;

use Domain\Role\DataTransferObjects\RoleData;
use Domain\Role\Exceptions\CantModifySuperAdminRoleException;
use Domain\Role\Models\Role;

class UpdateRoleAction
{
    public function execute(Role $role, RoleData $roleData): Role
    {
        if ($role->name === config('domain.role.super_admin')) {
            throw new CantModifySuperAdminRoleException;
        }

        $role->update(array_filter([
            'name' => $roleData->name,
            'guard_name' => $roleData->guard_name,
        ]));

        $role->syncPermissions($roleData->permissions);

        return $role;
    }
}
