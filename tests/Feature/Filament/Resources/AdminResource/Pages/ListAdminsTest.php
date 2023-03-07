<?php

declare(strict_types=1);

use App\Filament\Resources\AdminResource\Pages\ListAdmins;
use Domain\Admin\Database\Factories\AdminFactory;
use Filament\Pages\Actions\DeleteAction;
use Filament\Pages\Actions\ForceDeleteAction;
use Filament\Pages\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use STS\FilamentImpersonate\Impersonate;

use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertEquals;
use function PHPUnit\Framework\assertNotEquals;

beforeEach(fn () => loginAsSuperAdmin());

it('can list', function () {
    AdminFactory::new()->count(10)
        ->softDeleted()
        ->create();

    $admins = AdminFactory::new()->count(9) // include current logged in admin
        ->create();

    livewire(ListAdmins::class)
        ->assertCanSeeTableRecords($admins);
});

it('can list soft deleted', function () {
    $admins = AdminFactory::new()->count(10)
        ->softDeleted()
        ->create();

    AdminFactory::new()->count(9) // include current logged in admin
        ->create();

    livewire(ListAdmins::class)
        ->filterTable(TrashedFilter::class, false) // only trashed
        ->assertCanSeeTableRecords($admins);
});

it('can delete', function () {
    $admin = AdminFactory::new()
        ->createOne();

    livewire(ListAdmins::class)
        ->callTableAction(DeleteAction::class, $admin);

    assertSoftDeleted($admin);
});

it('can restore', function () {
    $admin = AdminFactory::new()
        ->softDeleted()
        ->createOne();

    livewire(ListAdmins::class)
        ->filterTable(TrashedFilter::class, false) // only trashed
        ->callTableAction(RestoreAction::class, $admin);

    assertNotSoftDeleted($admin);
});

it('can force delete', function () {
    $admin = AdminFactory::new()
        ->softDeleted()
        ->createOne();

    livewire(ListAdmins::class)
        ->filterTable(TrashedFilter::class, false) // only trashed
        ->callTableAction(ForceDeleteAction::class, $admin);

    assertModelMissing($admin);
});

it('can impersonate', function () {
    loginAsAdmin()->givePermissionTo('admin.viewAny', 'admin.impersonate');

    $admin = AdminFactory::new()->createOne();

    assertNotEquals($admin->getKey(), auth()->id());

    livewire(ListAdmins::class)
        ->assertOK()
        ->callTableAction(Impersonate::class, $admin);

    assertEquals($admin->getKey(), auth()->id());
});
