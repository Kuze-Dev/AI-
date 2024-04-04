<?php

declare(strict_types=1);

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

        if ($adminData->roles !== null) {
            $admin->roles()->sync($adminData->roles);
        }

        if ($adminData->permissions !== null) {
            $admin->permissions()->sync($adminData->permissions);
        }

        event(new Registered($admin));

        return $admin;
    }

    protected function getAdminAttributes(AdminData $adminData): array
    {
        return array_filter(
            [
                'first_name' => $adminData->first_name,
                'last_name' => $adminData->last_name,
                'email' => $adminData->email,
                'password' => $adminData->password,
                'active' => $adminData->active,
                'timezone' => $adminData->timezone,
            ],
            fn ($value) => filled($value)
        );
    }
}
