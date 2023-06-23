<?php

declare(strict_types=1);

use App\Filament\Resources\AdminResource\Pages\CreateAdmin;
use Domain\Admin\Models\Admin;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertCount;

beforeEach(fn () => loginAsSuperAdmin());

it('can create', function () {
    $admin = livewire(CreateAdmin::class)
        ->fillForm([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@user',
            'password' => 'password',
            'password_confirmation' => 'password',
            'active' => true,
            'roles' => [1],
            'permissions' => [1],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->instance()
        ->record;

    assertDatabaseHas(Admin::class, [
        'first_name' => 'Test',
        'last_name' => 'User',
        'email' => 'test@user',
        'active' => true,
    ]);

    assertCount(1, $admin->roles);
    assertCount(1, $admin->permissions);
});
