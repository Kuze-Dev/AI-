<?php

declare(strict_types=1);

use App\Filament\Resources\RoleResource\Pages\EditRole;
use App\Filament\Resources\RoleResource\Support\PermissionGroupCollection;
use Database\Factories\RoleFactory;

use function Pest\Livewire\livewire;

beforeEach(fn () => loginAsAdmin());

it('can render page', function () {
    $role = RoleFactory::new()->createOne();

    livewire(EditRole::class, ['record' => $role->getKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            'name' => $role->name,
            'guard_name' => $role->guard_name,
        ]);
});

it('can edit role', function () {
    $permissionGroups = PermissionGroupCollection::make(['guard_name' => 'admin']);
    $role = RoleFactory::new()->createOne();

    expect($role->permissions)->toHaveCount(0);

    livewire(EditRole::class, ['record' => $role->getKey()])
        ->fillForm([
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            $permissionGroups->keys()->first() => true
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($role->refresh()->permissions)->toHaveCount(1);
});
