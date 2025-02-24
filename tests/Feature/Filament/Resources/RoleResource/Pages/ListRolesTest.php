<?php

declare(strict_types=1);

use App\Filament\Resources\RoleResource\Pages\ListRoles;
use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Role\Database\Factories\RoleFactory;
use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Relations\Relation;
use Spatie\Permission\Models\Permission;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(fn () => loginAsSuperAdmin());

it('can list', function () {
    $roles = RoleFactory::new()->count(9)->create();

    livewire(ListRoles::class)
        ->assertCanSeeTableRecords($roles);
});

it('can delete', function () {
    $permission = Permission::first();

    $role = RoleFactory::new()->create();

    $role->permissions()->attach($permission);

    livewire(ListRoles::class)
        ->callTableAction(DeleteAction::class, $role);

    assertModelMissing($role);
    assertDatabaseMissing(
        config('permission.table_names.model_has_permissions'),
        [
            'model_type' => Relation::getMorphedModel($role::class),
            'model_id' => $role->id,
            'permission_id' => $permission->id,
        ]
    );
});

it('can not delete role with existing user', function () {
    $role = RoleFactory::new()
        ->createOne();
    $admin = AdminFactory::new()
        ->createOne();

    $admin->roles()->attach($role);

    livewire(ListRoles::class)
        ->callTableAction(DeleteAction::class, $role)
        ->assertNotified(trans(
            'Cannot Delete this Record',
           ));
});
