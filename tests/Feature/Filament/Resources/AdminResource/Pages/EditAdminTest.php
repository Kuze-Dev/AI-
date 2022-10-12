<?php

declare(strict_types=1);

use App\Filament\Resources\AdminResource;

use Database\Factories\AdminFactory;
use Illuminate\Auth\Middleware\RequirePassword;

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

    livewire(AdminResource\Pages\EditAdmin::class, [
        'record' => $admin->getKey(),
    ])
        ->assertFormSet([
            'first_name' => $admin->first_name,
            'last_name' => $admin->last_name,
            'email' => $admin->email,
            'active' => $admin->active,
            'roles' => [],
            'permissions' => [],
        ]);
});

it('can save', function () {
    $admin = AdminFactory::new()
        ->active()
        ->createOne();
    $newData = AdminFactory::new()
        ->active()
        ->make();

    livewire(AdminResource\Pages\EditAdmin::class, [
        'record' => $admin->getKey(),
    ])
        ->fillForm([
            'first_name' => $newData->first_name,
            'last_name' => $newData->last_name,
            'email' => $newData->email,
            'active' => $newData->active,
            'roles' => [],
            'permissions' => [],
        ])
        ->call('save')
        ->assertHasNoFormErrors();
});
