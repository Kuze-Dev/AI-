<?php

declare(strict_types=1);

namespace Database\Seeders\Auth;

use Domain\Role\Database\Factories\RoleFactory;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        RoleFactory::new(['name' => config()->string('domain.role.super_admin')])
            ->createOne();
    }
}
