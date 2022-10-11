<?php

namespace Domain\Admin\Actions;

use Domain\Admin\Models\Admin;

class RestoreAdminAction
{
    public function execute(Admin $admin): ?bool
    {
        if (! $admin->trashed()) {
            return null;
        }

        return $admin->restore();
    }
}
