<?php

use Domain\Admin\Actions\CreateRoleAction;
use Domain\Admin\DataTransferObjects\RoleData;
use function Pest\Laravel\assertModelExists;
use Spatie\Permission\Models\Permission;

it('can create role', function () {
    $permission = Permission::create(['name' => 'admin.view']);

    $role = app(CreateRoleAction::class)->execute(new RoleData(
        name: 'Admin',
        permissions: [$permission->id],
    ));

    assertModelExists($role);
    expect($role->hasPermissionTo($permission))->toBeTrue();
});
