<?php

declare(strict_types=1);

use App\Filament\Resources\AdminResource;

use Database\Factories\AdminFactory;
use Illuminate\Auth\Middleware\RequirePassword;

use Tests\RequestFactories\AdminRequestFactory;

use function Pest\Laravel\get;
use function Pest\Laravel\withoutMiddleware;
use function Pest\Livewire\livewire;

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

    livewire(AdminResource\Pages\EditAdmin::class, [
        'record' => $admin->getKey(),
    ])
        ->fillForm(AdminRequestFactory::new()->create())
        ->call('save')
        ->assertHasNoFormErrors();
});
