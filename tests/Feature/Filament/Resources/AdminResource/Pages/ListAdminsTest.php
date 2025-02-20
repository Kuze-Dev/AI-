<?php

declare(strict_types=1);

use App\Filament\Resources\AdminResource\Pages\ListAdmins;
use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Role\Models\Role;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Facades\Filament;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Contracts\Permission;
use STS\FilamentImpersonate\Impersonate;

use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\freezeTime;
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

    //    assertActivityLogged(
    //        logName: 'admin',
    //        event: 'deleted',
    //        causedBy: Filament::auth()->user(),
    //        subject: $admin
    //    );
});

it('can restore', function () {
    $admin = AdminFactory::new()
        ->softDeleted()
        ->createOne();

    livewire(ListAdmins::class)
        ->filterTable(TrashedFilter::class)
        ->callTableAction(RestoreAction::class, $admin);

    assertNotSoftDeleted($admin);

    //    assertActivityLogged(
    //        logName: 'admin',
    //        event: 'restored',
    //        causedBy: Filament::auth()->user(),
    //        subject: $admin
    //    );
});

it('can force delete', function () {
    $admin = AdminFactory::new()
        ->softDeleted()
        ->createOne();

    livewire(ListAdmins::class)
        ->filterTable(TrashedFilter::class)
        ->callTableAction(ForceDeleteAction::class, $admin);

    assertModelMissing($admin);

    //    assertActivityLogged(
    //        logName: 'admin',
    //        event: 'force-deleted',
    //        causedBy: Filament::auth()->user(),
    //        subject: $admin
    //    );
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

    loginAsAdmin()->givePermissionTo([
        app(Permission::class)
            ->create(['name' => 'admin.viewAny']),

        app(Permission::class)
        ->create(['name' => 'admin.impersonate']),
   ] );

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

it('can bulk export', function () {

    config(['filament-export.temporary_files.disk' => 'local']);

    $admins = AdminFactory::new()->count(3)->create();

    freezeTime();
    //    \Illuminate\Support\Facades\Queue::fake([DefaultExport::class]);

    livewire(ListAdmins::class)
        ->callTableBulkAction(
            ExportBulkAction::class,
            $admins
        );

    assertActivityLogged(
        logName: 'admin',
        event: 'bulk-exported',
        description: 'Bulk Exported Admin',
        properties: ['selected_record_ids' => $admins->modelKeys()],
        causedBy: Filament::auth()->user(),
    );
})->todo();

it('can export', function () {
    AdminFactory::new()->count(3)->create();

    freezeTime();

    livewire(ListAdmins::class)
        ->callPageAction(
            ExportAction::class,
        );

    assertActivityLogged(
        logName: 'admin',
        event: 'exported',
        description: 'Exported Admin',
        causedBy: Filament::auth()->user(),
    );
})->todo();

it('can import', function () {
    $file = csvFiles(fn () => [
        fake()->unique()->safeEmail(),
        fake()->firstName(),
        fake()->lastName(),
        fake()->boolean() ? 'Yes' : 'No',
        Role::inRandomOrder()
            ->take(Arr::random(range(1, Role::count())))
            ->pluck('name')
            ->implode(', '),
        fake()->timezone(),
    ], 4);

    livewire(ListAdmins::class)
        ->callPageAction(
            ImportAction::class,
            ['file' => [$file->store('tmp')]]
        );

    // TODO: add activity log on import package
    //    assertActivityLogged(
    //        logName: 'admin',
    //        event: 'imported',
    //        description: 'Imported Admin',
    //        causedBy: Filament::auth()->user(),
    //    );
})->todo();
