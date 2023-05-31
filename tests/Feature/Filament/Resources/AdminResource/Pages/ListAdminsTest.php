<?php

declare(strict_types=1);

use App\Filament\Resources\AdminResource\Pages\ListAdmins;
use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Admin\Notifications\ResetPassword;
use Filament\Facades\Filament;
use Filament\Pages\Actions\DeleteAction;
use Filament\Pages\Actions\ForceDeleteAction;
use Filament\Pages\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Facades\Auth;
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

    assertActivityLogged(
        logName: 'admin',
        event: 'deleted',
        causedBy: Filament::auth()->user(),
        subject: $admin
    );
});

it('can restore', function () {
    $admin = AdminFactory::new()
        ->softDeleted()
        ->createOne();

    livewire(ListAdmins::class)
        ->filterTable(TrashedFilter::class, false) // only trashed
        ->callTableAction(RestoreAction::class, $admin);

    assertNotSoftDeleted($admin);

    assertActivityLogged(
        logName: 'admin',
        event: 'restored',
        causedBy: Filament::auth()->user(),
        subject: $admin
    );
});

it('can force delete', function () {
    $admin = AdminFactory::new()
        ->softDeleted()
        ->createOne();

    livewire(ListAdmins::class)
        ->filterTable(TrashedFilter::class, false) // only trashed
        ->callTableAction(ForceDeleteAction::class, $admin);

    assertModelMissing($admin);

    assertActivityLogged(
        logName: 'admin',
        event: 'force-deleted',
        causedBy: Filament::auth()->user(),
        subject: $admin
    );
});

it('can send password reset link', function () {
    $admin = AdminFactory::new()
        ->createOne();

    Notification::fake();

    livewire(ListAdmins::class)
        ->callTableAction('send-password-reset', $admin);

    Notification::assertSentTo($admin, ResetPassword::class);

    assertActivityLogged(
        logName: 'admin',
        event: 'password-reset-link-sent',
        causedBy: Filament::auth()->user(),
        subject: $admin
    );
});

it('can impersonate', function () {
    loginAsAdmin()->givePermissionTo('admin.viewAny', 'admin.impersonate');

    $admin = AdminFactory::new()->createOne();

    $initialAuthenticatedAdmin = Auth::user();

    assertNotEquals($admin->getKey(), $initialAuthenticatedAdmin->getKey());

    livewire(ListAdmins::class)
        ->assertOK()
        ->callTableAction(Impersonate::class, $admin);

    assertEquals($admin->getKey(), auth()->id());

    assertActivityLogged(
        logName: 'admin',
        event: 'impersonated',
        causedBy: $initialAuthenticatedAdmin,
        subject: $admin
    );
});
