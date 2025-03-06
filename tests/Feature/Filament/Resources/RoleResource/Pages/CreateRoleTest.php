<?php

declare(strict_types=1);

use App\Filament\Resources\RoleResource\Pages\CreateRole;
use App\Filament\Resources\RoleResource\Support\PermissionGroupCollection;
use Spatie\Permission\Models\Role;
use Tests\TestSeeder;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;
use function Pest\Livewire\livewire;

beforeEach(function () {
    seed(TestSeeder::class);
    loginAsSuperAdmin();
});

it('can create', function () {

    livewire(CreateRole::class)
        ->fillForm([
            'name' => 'Foo',
            'guard_name' => 'admin',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->instance()
        ->record;

    assertDatabaseHas(Role::class, [
        'name' => 'Foo',
        'guard_name' => 'admin',
    ]);
});

it('doesn\'t show any permission when no guard is selected', function () {
    livewire(CreateRole::class)
        ->fillForm(['guard_name' => null])
        ->assertSee(trans('Please select a guard'));
});

it('can select all permissions', function () {
    $permissionGroups = PermissionGroupCollection::make(['guard_name' => 'admin']);

    /** @var CreateRole $component */
    $component = livewire(CreateRole::class)
        ->fillForm([
            'name' => 'Foo',
            'guard_name' => 'admin',
        ])
        ->instance();

    $component->form->getComponent('data.select_all')
        ->state(true)
        ->callAfterStateUpdated();

    expect($component->form->getState()['permissions'])
        ->toHaveCount($permissionGroups->count());
});

it('can toggle permission group to select all abilities', function () {
    $permissionGroups = PermissionGroupCollection::make(['guard_name' => 'admin']);

    /** @var CreateRole $component */
    $component = livewire(CreateRole::class)
        ->fillForm([
            'name' => 'Foo',
            'guard_name' => 'admin',
        ])
        ->instance();

    $component->form->getComponent("data.{$permissionGroups->keys()->first()}")
        ->state(true)
        ->callAfterStateUpdated();

    expect($component->form->getComponent("data.{$permissionGroups->keys()->first()}_abilities")->getState())
        ->toContain(...$permissionGroups->first()->abilities->pluck('id')->toArray());
    expect($component->form->getState()['permissions'])
        ->toContain($permissionGroups->first()->main->id);
});

it('can toggle permission group when all abilites selected', function () {
    $permissionGroups = PermissionGroupCollection::make(['guard_name' => 'admin']);

    /** @var CreateRole $component */
    $component = livewire(CreateRole::class)
        ->fillForm([
            'name' => 'Foo',
            'guard_name' => 'admin',
        ])
        ->instance();

    $component->form->getComponent("data.{$permissionGroups->keys()->first()}_abilities")
        ->state($permissionGroups->first()->abilities->pluck('id')->toArray())
        ->callAfterStateUpdated();

    expect($component->form->getState()['permissions'])
        ->toContain($permissionGroups->first()->main->id)
        ->not()->toContain(...$permissionGroups->first()->abilities->pluck('id')->toArray());
});
