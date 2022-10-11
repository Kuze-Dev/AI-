<?php

namespace Database\Seeders\Auth;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create([
            'id' => 1,
            'name' => config('domain.admin.role.super_admin'),
        ]);
    }
}
