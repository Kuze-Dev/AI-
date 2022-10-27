<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Auth;

use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Admin\Models\Admin;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        /** @var Admin $superAdmin */
        $superAdmin = AdminFactory::new([
            'first_name' => 'System',
            'last_name' => 'Administrator',
        ])
            ->create();

        /** @phpstan-ignore-next-line */
        $superAdmin->syncRoles(Role::whereName(config('domain.role.super_admin'))->first());
    }
}
