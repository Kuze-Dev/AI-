<?php

declare(strict_types=1);

use App\Filament\Resources\AdminResource\Pages\EditAdmin;
use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Admin\Models\Admin;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(fn () => loginAsSuperAdmin());

it('can show edit', function () {
    $admin = AdminFactory::new()
        ->active()
        ->createOne();

    $admin->assignRole(1);
    $admin->givePermissionTo(2);

    livewire(EditAdmin::class, ['record' => $admin->getKey()])
        ->assertFormSet([
            'first_name' => $admin->first_name,
            'last_name' => $admin->last_name,
            'email' => $admin->email,
            'active' => $admin->active,
            'roles' => [1],
            'permissions' => [2],
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
