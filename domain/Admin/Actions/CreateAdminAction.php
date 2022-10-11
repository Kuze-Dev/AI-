<?php

namespace Domain\Admin\Actions;

use Domain\Admin\DataTransferObjects\AdminData;
use Domain\Admin\Models\Admin;
use Illuminate\Auth\Events\Registered;

class CreateAdminAction
{
    public function execute(AdminData $adminData): Admin
    {
        /** @var Admin $admin */
        $admin = Admin::create($this->getAdminAttributes($adminData));

        $admin->syncRoles($adminData->roles);
        $admin->syncPermissions($adminData->permissions);

        event(new Registered($admin));

        return $admin;
    }

    protected function getAdminAttributes(AdminData $adminData): array
    {
        return array_filter([
            'first_name' => $adminData->first_name,
            'last_name' => $adminData->last_name,
            'email' => $adminData->email,
            'password' => $adminData->password,
            'active' => $adminData->active,
            'timezone' => $adminData->timezone,
        ]);
    }
}
