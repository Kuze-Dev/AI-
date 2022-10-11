<?php

declare(strict_types=1);

namespace Domain\Admin\Actions;

use Domain\Admin\Exceptions\CantDeleteSuperAdminException;
use Domain\Admin\Models\Admin;

class DeleteAdminAction
{
    public function execute(Admin $admin, bool $force = false): bool
    {
        if ($admin->isSuperAdmin()) {
            throw new CantDeleteSuperAdminException();
        }

        return $admin->{$force ? 'forceDelete' : 'delete'}();
    }
}
