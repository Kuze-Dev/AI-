<?php

declare(strict_types=1);

use App\Filament\Resources\AdminResource\Pages\EditAdmin;
use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Admin\Models\Admin;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\Role;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(fn () => loginAsSuperAdmin());

it('can show edit', function () {
    $admin = AdminFactory::new()
        ->active()
        ->createOne();

    $role = app(Role::class)
        ->create(['name' => fake()->name]);
    $permission = app(Permission::class)
        ->create(['name' => fake()->name]);

    $admin->assignRole($role);
    $admin->givePermissionTo($permission);

    livewire(EditAdmin::class, ['record' => $admin->getKey()])
        ->assertFormSet([
            'first_name' => $admin->first_name,
            'last_name' => $admin->last_name,
            'email' => $admin->email,
            'active' => $admin->active,
            'roles' => [$role->getKey()],
            'permissions' => [$permission->getKey()],
        ]);
});

it('can update', function () {
    $admin = AdminFactory::new()
        ->active(false)
        ->createOne();

    livewire(EditAdmin::class, ['record' => $admin->getKey()])
        ->fillForm([
            'first_name' => 'Test',
            'last_name' => 'User',
            'active' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Admin::class, [
        'first_name' => 'Test',
        'last_name' => 'User',
        'active' => true,
    ]);
});
