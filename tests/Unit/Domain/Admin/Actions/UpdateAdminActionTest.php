<?php

declare(strict_types=1);

use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Admin\Actions\UpdateAdminAction;
use Domain\Admin\DataTransferObjects\AdminData;
use Domain\Admin\Notifications\VerifyEmail;
use Illuminate\Contracts\Notifications\Dispatcher;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\assertDatabaseHas;

it('can update admin', function () {
    $admin = AdminFactory::new()->create();
    $initialEmail = $admin->email;
    $role = Role::create(['name' => 'Admin']);
    $permission = Permission::create(['name' => 'admin.view']);

    $admin = app(UpdateAdminAction::class)->execute($admin, new AdminData(
        first_name: 'John',
        last_name: 'Doe',
        email: 'test@user',
        roles: [$role->id],
        permissions: [$permission->id]
    ));

    assertDatabaseHas(
        'admins',
        [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $initialEmail,
        ]
    );
    expect($admin->hasRole($role))->toBeTrue();
    expect($admin->hasPermissionTo($permission))->toBeTrue();
});

it('resend email verification when email updated', function () {
    config(['domain.admin.can_change_email' => true]);
    $admin = AdminFactory::new()->create();
    $role = Role::create(['name' => 'Admin']);
    $permission = Permission::create(['name' => 'admin.view']);
    $this->mock(
        Dispatcher::class,
        fn (MockInterface $mock) => $mock->expects('send')
            ->with($admin, VerifyEmail::class)
    );

    $admin = app(UpdateAdminAction::class)->execute($admin, new AdminData(
        first_name: 'John',
        last_name: 'Doe',
        email: 'test@user',
        roles: [$role->id],
        permissions: [$permission->id]
    ));

    assertDatabaseHas(
        'admins',
        [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'test@user',
        ]
    );
    expect($admin->hasRole($role))->toBeTrue();
    expect($admin->hasPermissionTo($permission))->toBeTrue();
});
