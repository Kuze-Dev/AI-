<?php

declare(strict_types=1);

namespace Domain\Admin\Actions;

use Domain\Admin\DataTransferObjects\RoleData;
use Spatie\Permission\Models\Role;

class CreateRoleAction
{
    public function execute(RoleData $roleData): Role
    {
        /** @var Role $role */
        $role = Role::create(array_filter([
            'name' => $roleData->name,
            'guard_name' => $roleData->guard_name,
        ]));

        $role->syncPermissions($roleData->permissions);

        return $role;
    }
}
