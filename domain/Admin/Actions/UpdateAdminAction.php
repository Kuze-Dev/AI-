<?php

declare(strict_types=1);

namespace Domain\Admin\Actions;

use Domain\Admin\DataTransferObjects\AdminData;
use Domain\Admin\Models\Admin;

class UpdateAdminAction
{
    public function execute(Admin $admin, AdminData $adminData): Admin
    {
        $admin->update($this->getAdminAttributes($adminData));

        if ($adminData->roles !== null) {
            $admin->roles()->sync($adminData->roles);
        }

        if ($adminData->permissions !== null) {
            $admin->permissions()->sync($adminData->permissions);
        }

        if ($admin->wasChanged('email')) {
            $admin->forceFill(['email_verified_at' => null])
                ->save();

            $admin->sendEmailVerificationNotification();
        }

        return $admin;
    }

    protected function getAdminAttributes(AdminData $adminData): array
    {
        return array_filter(
            [
                'first_name' => $adminData->first_name,
                'last_name' => $adminData->last_name,
                'email' => config('domain.admin.can_change_email') ? $adminData->email : null,
                'password' => $adminData->password,
                'active' => $adminData->active,
                'timezone' => $adminData->timezone,
            ],
            fn ($value) => filled($value)
        );
    }
}
