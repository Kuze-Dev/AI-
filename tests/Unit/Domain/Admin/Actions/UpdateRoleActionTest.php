<?php

use Domain\Admin\Actions\UpdateRoleAction;
use Domain\Admin\DataTransferObjects\RoleData;
use Domain\Admin\Exceptions\CantModifySuperAdminRoleException;
use function Pest\Laravel\assertDatabaseHas;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('can update role', function () {
    $role = Role::create(['name' => 'Admin']);
    $permission = Permission::create(['name' => 'admin.view']);

    $role = app(UpdateRoleAction::class)->execute($role, new RoleData(
        name: 'Administrator',
        permissions: [$permission->id],
    ));

    assertDatabaseHas(
        config('permission.table_names.roles'),
        ['name' => 'Administrator']
    );
    expect($role->hasPermissionTo($permission))->toBeTrue();
});

it('can\' update super admin role', function () {
    $role = Role::create(['name' => config('domain.admin.role.super_admin')]);

    app(UpdateRoleAction::class)->execute($role, new RoleData(name: 'Administrator'));
})->throws(CantModifySuperAdminRoleException::class);
