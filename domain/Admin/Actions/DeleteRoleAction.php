<?php

namespace Domain\Admin\Actions;

use Domain\Admin\Exceptions\CantDeleteRoleWithAssociatedUsersException;
use Domain\Admin\Exceptions\CantDeleteSuperAdminRoleException;
use Spatie\Permission\Models\Role;

class DeleteRoleAction
{
    public function execute(Role $role): ?bool
    {
        if ($role->name === config('domain.admin.role.super_admin')) {
            throw new CantDeleteSuperAdminRoleException();
        }

        if ($role->users()->count()) {
            throw new CantDeleteRoleWithAssociatedUsersException();
        }

        return $role->delete();
    }
}
