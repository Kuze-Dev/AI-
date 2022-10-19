<?php

declare(strict_types=1);

use Domain\Role\Actions\CreateRoleAction;
use Domain\Role\DataTransferObjects\RoleData;
use Spatie\Permission\Models\Permission;

use function Pest\Laravel\assertModelExists;

it('can create role', function () {
    $permission = Permission::create(['name' => 'admin.view']);

    $role = app(CreateRoleAction::class)->execute(new RoleData(
        name: 'Admin',
        permissions: [$permission->id],
    ));

    assertModelExists($role);
    expect($role->hasPermissionTo($permission))->toBeTrue();
});
