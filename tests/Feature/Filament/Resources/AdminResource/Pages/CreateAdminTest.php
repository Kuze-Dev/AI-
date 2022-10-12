<?php

declare(strict_types=1);

use App\Filament\Resources\AdminResource;
use Domain\Admin\Models\Admin;
use Illuminate\Auth\Middleware\RequirePassword;

use Tests\RequestFactories\AdminRequestFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\get;
use function Pest\Laravel\withoutMiddleware;
use function Pest\Livewire\livewire;

beforeEach(fn () => loginAsAdmin());

it('can render page', function () {
    withoutMiddleware(RequirePassword::class);

    get(AdminResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create', function () {
    assertDatabaseCount(Admin::class, 1); // on logged in user

    livewire(AdminResource\Pages\CreateAdmin::class)
        ->fillForm(
            AdminRequestFactory::new()
                ->roles([1])
                ->permissions([2])
                ->create()
        )
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseCount(Admin::class, 2);
});
