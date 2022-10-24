<?php

declare(strict_types=1);

use App\Filament\Resources\AdminResource;

use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Admin\Models\Admin;
use Illuminate\Auth\Middleware\RequirePassword;

use Tests\RequestFactories\AdminRequestFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\get;
use function Pest\Laravel\withoutMiddleware;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertSame;

beforeEach(fn () => loginAsAdmin());

it('can render page', function () {
    withoutMiddleware(RequirePassword::class);

    $admin = AdminFactory::new()
        ->createOne();

    get(AdminResource::getUrl('edit', $admin))
        ->assertSuccessful();
});

it('can retrieve data', function () {
    $admin = AdminFactory::new()
        ->active()
        ->createOne();

    $admin->assignRole(1);
    $admin->givePermissionTo(2);

    livewire(AdminResource\Pages\EditAdmin::class, [
        'record' => $admin->getKey(),
    ])
        ->assertFormSet([
            'first_name' => $admin->first_name,
            'last_name' => $admin->last_name,
            'email' => $admin->email,
            'active' => $admin->active,
            'roles' => [1],
            'permissions' => [2],
        ]);
});

it('can save', function () {
    $admin = AdminFactory::new()
        ->active()
        ->createOne();

    assertDatabaseCount(Admin::class, 2); // with logged-in user

    livewire(AdminResource\Pages\EditAdmin::class, [
        'record' => $admin->getKey(),
    ])
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

    livewire(AdminResource\Pages\EditAdmin::class, [
        'record' => $admin->getKey(),
    ])
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
