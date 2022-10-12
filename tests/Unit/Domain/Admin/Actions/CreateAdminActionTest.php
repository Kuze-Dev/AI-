<?php

declare(strict_types=1);

use Domain\Admin\Actions\CreateAdminAction;
use Domain\Admin\DataTransferObjects\AdminData;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\assertModelExists;

it('can create admin', function () {
    $role = Role::create(['name' => 'Admin']);
    $permission = Permission::create(['name' => 'admin.view']);

    Event::fake();

    $admin = app(CreateAdminAction::class)->execute(new AdminData(
        first_name: 'John',
        last_name: 'Doe',
        email: 'test@user',
        password: 'secret',
        roles: [$role->id],
        permissions: [$permission->id]
    ));

    assertModelExists($admin);
    expect($admin->hasRole($role))->toBeTrue();
    expect($admin->hasPermissionTo($permission))->toBeTrue();
    Event::assertDispatched(Registered::class);
});
