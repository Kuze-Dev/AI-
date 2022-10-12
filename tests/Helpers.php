<?php

declare(strict_types=1);

use Database\Factories\AdminFactory;
use Domain\Admin\Models\Admin;

use function Pest\Laravel\actingAs;

function loginAsAdmin(Admin $admin = null): Admin
{
    $admin ??= AdminFactory::new()
        ->active()
        ->createOne();

    $admin->assignRole(config('domain.admin.role.super_admin'));

    actingAs($admin);

    return $admin;
}
