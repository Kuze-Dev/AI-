<?php

declare(strict_types=1);

namespace Domain\Admin\Actions;

use Domain\Admin\Exceptions\CantDeleteZeroDayAdminException;
use Domain\Admin\Models\Admin;

class DeleteAdminAction
{
    public function execute(Admin $admin, bool $force = false): bool
    {
        if ($admin->isZeroDayAdmin()) {
            throw new CantDeleteZeroDayAdminException();
        }

        return $admin->{$force ? 'forceDelete' : 'delete'}();
    }
}
