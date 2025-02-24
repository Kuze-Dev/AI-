<?php

declare(strict_types=1);

namespace Domain\Role\Actions;

use Domain\Role\Exceptions\CantDeleteRoleWithAssociatedUsersException;
use Domain\Role\Exceptions\CantDeleteSuperAdminRoleException;
use Domain\Role\Models\Role;

class DeleteRoleAction
{
    public function execute(Role $role): ?bool
    {

        if ($role->name === config('domain.role.super_admin')) {
            throw new CantDeleteSuperAdminRoleException();
        }

        if ($role->users()->count()) {
            throw new CantDeleteRoleWithAssociatedUsersException();
        }

        return $role->delete();
    }
}
