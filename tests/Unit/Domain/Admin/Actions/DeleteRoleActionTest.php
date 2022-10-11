<?php

use Database\Factories\AdminFactory;
use Domain\Admin\Actions\DeleteRoleAction;
use Domain\Admin\Exceptions\CantDeleteRoleWithAssociatedUsersException;
use Domain\Admin\Exceptions\CantDeleteSuperAdminRoleException;
use function Pest\Laravel\assertModelMissing;
use Spatie\Permission\Models\Role;

it('can delete role', function () {
    $role = Role::create(['name' => 'Admin']);

    $result = app(DeleteRoleAction::class)->execute($role);

    assertModelMissing($role);
    expect($result)->toBeTrue();
});

it('can\' delete super admin role', function () {
    $role = Role::create(['name' => config('domain.admin.role.super_admin')]);

    app(DeleteRoleAction::class)->execute($role);
})->throws(CantDeleteSuperAdminRoleException::class);

it('can\' delete role with assigned users', function () {
    $role = Role::create(['name' => 'Admin']);
    $admin = AdminFactory::new()->create();
    $admin->syncRoles($role);

    app(DeleteRoleAction::class)->execute($role);
})->throws(CantDeleteRoleWithAssociatedUsersException::class);
