<?php

declare(strict_types=1);

use Domain\Role\Actions\UpdateRoleAction;
use Domain\Role\DataTransferObjects\RoleData;
use Domain\Role\Exceptions\CantModifySuperAdminRoleException;
use Domain\Role\Models\Role;
use Spatie\Permission\Models\Permission;

use function Pest\Laravel\assertDatabaseHas;

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
    $role = Role::create(['name' => config()->string('domain.role.super_admin')]);

    app(UpdateRoleAction::class)->execute($role, new RoleData(name: 'Administrator'));
})->throws(CantModifySuperAdminRoleException::class);
