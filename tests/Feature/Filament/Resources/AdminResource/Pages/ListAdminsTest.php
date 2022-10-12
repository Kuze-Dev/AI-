<?php

declare(strict_types=1);

use App\Filament\Resources\AdminResource;
use App\Filament\Resources\AdminResource\Pages\ListAdmins;
use Database\Factories\AdminFactory;
use Illuminate\Auth\Middleware\RequirePassword;

use function Pest\Laravel\get;
use function Pest\Laravel\withoutMiddleware;
use function Pest\Livewire\livewire;

beforeEach(fn () => loginAsAdmin());

it('can render page', function () {
    withoutMiddleware(RequirePassword::class);

    get(AdminResource::getUrl())
        ->assertSuccessful();
});

it('can list admins', function () {
    AdminFactory::new()->count(10)
        ->softDeleted()
        ->create();

    $admins = AdminFactory::new()->count(9) // include current logged in admin
        ->create();

    livewire(ListAdmins::class)
        ->assertCanSeeTableRecords($admins);
});
