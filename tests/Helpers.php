<?php

declare(strict_types=1);

use Database\Factories\AdminFactory;
use Domain\Admin\Models\Admin;

use function Pest\Laravel\actingAs;

function loginAsAdmin(Admin $admin = null): Admin
{
    $admin = loginAsUser($admin);

    $admin->assignRole(config('domain.admin.role.super_admin'));

    return $admin;
}

function loginAsUser(Admin $admin = null): Admin
{
    $admin ??= AdminFactory::new()
        ->active()
        ->createOne();

    actingAs($admin);

    return $admin;
}
