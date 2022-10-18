<?php

declare(strict_types=1);

use App\Filament\Resources\RoleResource\Pages\ListRoles;
use Database\Factories\RoleFactory;
use Filament\Pages\Actions\DeleteAction;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(fn () => loginAsAdmin());

it('can render list role', function () {
    livewire(ListRoles::class)->assertSuccessful();
});

it('can list roles', function () {
    $roles = RoleFactory::new()->count(9)->create();

    livewire(ListRoles::class)
        ->assertCanSeeTableRecords($roles);
});

it('can delete role', function () {
    $role = RoleFactory::new()->create();

    livewire(ListRoles::class)
        ->callTableAction(DeleteAction::class, $role);

    assertModelMissing($role);
});
