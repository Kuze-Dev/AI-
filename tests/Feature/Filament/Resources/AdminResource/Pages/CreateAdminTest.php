<?php

declare(strict_types=1);

use App\Filament\Resources\AdminResource\Pages\CreateAdmin;
use Domain\Admin\Models\Admin;
use Tests\RequestFactories\AdminRequestFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertSame;

beforeEach(fn () => loginAsAdmin());

it('can create', function () {
    assertDatabaseCount(Admin::class, 1); // on logged in user

    livewire(CreateAdmin::class)
        ->fillForm(
            AdminRequestFactory::new()
                ->create()
        )
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseCount(Admin::class, 2);
});

it('can create with roles', function () {
    assertDatabaseCount(Admin::class, 1); // with logged-in user

    livewire(CreateAdmin::class)
        ->fillForm(
            AdminRequestFactory::new()
                ->roles([1])
                ->permissions([])
                ->create()
        )
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseCount(Admin::class, 2);

    $newAdmin = Admin::whereKeyNot(auth()->user())->first();

    assertCount(1, $newAdmin->roles);
    assertCount(0, $newAdmin->permissions);
});

it('can create with permissions', function () {
    assertDatabaseCount(Admin::class, 1); // with logged-in user

    livewire(CreateAdmin::class)
        ->fillForm(
            AdminRequestFactory::new()
                ->roles([])
                ->permissions([1])
                ->create()
        )
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseCount(Admin::class, 2);

    $newAdmin = Admin::whereKeyNot(auth()->user())->first();

    assertCount(0, $newAdmin->roles);
    assertCount(1, $newAdmin->permissions);
});

it('can create with active', function (bool $active) {
    assertDatabaseCount(Admin::class, 1); // with logged-in user

    livewire(CreateAdmin::class)
        ->fillForm(
            AdminRequestFactory::new()
                ->active($active)
                ->create()
        )
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseCount(Admin::class, 2);

    $newAdmin = Admin::whereKeyNot(auth()->user())->first();

    assertSame($active, $newAdmin->active);
})
    ->with([
        'active' => true,
        'inactive' => false,
    ]);
