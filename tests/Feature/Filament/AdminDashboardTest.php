<?php

declare(strict_types=1);

use Domain\Admin\Database\Factories\AdminFactory;

use function Pest\Laravel\get;

it('redirect to verification notice', function () {
    $admin = AdminFactory::new()
        ->unverified()
        ->createOne();

    loginAsAdmin($admin);

    get('/admin')
        ->assertRedirect('admin/verify');
});

it('redirect to deactivation notice', function () {
    $admin = AdminFactory::new()
        ->active(false)
        ->createOne();

    loginAsAdmin($admin);

    get('/admin')
        ->assertRedirect('admin/account-deactivated');
});
