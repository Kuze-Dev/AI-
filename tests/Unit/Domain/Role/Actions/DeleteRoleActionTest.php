<?php

declare(strict_types=1);

use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Role\Actions\DeleteRoleAction;
use Domain\Role\Exceptions\CantDeleteRoleWithAssociatedUsersException;
use Domain\Role\Exceptions\CantDeleteSuperAdminRoleException;
use Domain\Role\Models\Role;

use function Pest\Laravel\assertModelMissing;

it('can delete role', function () {
    $role = Role::create(['name' => 'Admin']);

    $result = app(DeleteRoleAction::class)->execute($role);

    assertModelMissing($role);
    expect($result)->toBeTrue();
});

it('can\' delete super admin role', function () {
    $role = Role::create(['name' => config('domain.role.super_admin')]);

    app(DeleteRoleAction::class)->execute($role);
})->throws(CantDeleteSuperAdminRoleException::class);

it('can\' delete role with assigned users', function () {
    $role = Role::create(['name' => 'Admin']);
    $admin = AdminFactory::new()->create();
    $admin->syncRoles($role);

    app(DeleteRoleAction::class)->execute($role);
})->throws(CantDeleteRoleWithAssociatedUsersException::class);
