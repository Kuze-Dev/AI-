<?php

declare(strict_types=1);

namespace Database\Seeders\Auth;

use Database\Factories\AdminFactory;
use Domain\Admin\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Admin $superAdmin */
        $superAdmin = AdminFactory::new([
            'first_name' => 'Super',
            'last_name' => 'Admin',
        ])
            ->when(
                app()->isProduction(),
                fn (AdminFactory $adminFactory) => $adminFactory->passwordPrompt($this->command)
            )
            ->create();

        /** @phpstan-ignore-next-line */
        $superAdmin->syncRoles(Role::whereName(config('domain.role.super_admin'))->first());
    }
}
