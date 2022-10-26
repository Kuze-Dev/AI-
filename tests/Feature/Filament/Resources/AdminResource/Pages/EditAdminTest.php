<?php

declare(strict_types=1);

use App\Filament\Resources\AdminResource\Pages\EditAdmin;
use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Admin\Models\Admin;
use Tests\RequestFactories\AdminRequestFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertSame;

beforeEach(fn () => loginAsAdmin());

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
        ->active()
        ->createOne();

    assertDatabaseCount(Admin::class, 2); // with logged-in user

    livewire(EditAdmin::class, ['record' => $admin->getKey()])
        ->fillForm(AdminRequestFactory::new()->create())
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseCount(Admin::class, 2); // with logged-in user
});

it('can update with active', function (bool $active) {
    $admin = AdminFactory::new()
        ->active()
        ->createOne();

    assertDatabaseCount(Admin::class, 2); // with logged-in user

    livewire(EditAdmin::class, ['record' => $admin->getKey()])
        ->fillForm(
            AdminRequestFactory::new()
                ->active($active)
                ->create()
        )
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseCount(Admin::class, 2); // with logged-in user

    $newAdmin = Admin::whereKeyNot(auth()->user())->first();

    assertSame($active, $newAdmin->active);
})
    ->with([
        'active' => true,
        'inactive' => false,
    ]);
