<?php

declare(strict_types=1);

use App\Filament\Resources\AdminResource\Pages\ListAdmins;
use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Admin\Notifications\ResetPassword;
use Domain\Role\Models\Role;
use Filament\Facades\Filament;
use Filament\Pages\Actions\DeleteAction;
use Filament\Pages\Actions\ForceDeleteAction;
use Filament\Pages\Actions\RestoreAction;
use Filament\Tables\Filters\TrashedFilter;
use HalcyonAgile\FilamentExport\Actions\ExportAction;
use HalcyonAgile\FilamentExport\Actions\ExportBulkAction;
use HalcyonAgile\FilamentExport\Export\DefaultExport;
use HalcyonAgile\FilamentImport\Actions\ImportAction;
use HalcyonAgile\FilamentImport\DefaultImport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use STS\FilamentImpersonate\Impersonate;

use function Pest\Laravel\assertModelMissing;
use function Pest\Laravel\assertNotSoftDeleted;
use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\freezeTime;
use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertCount;
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

it('can bulk export', function () {

    config(['filament-export.temporary_files.disk' => 'local']);

    $admins = AdminFactory::new()->count(3)->create();

    freezeTime();
    Excel::fake();
    //    \Illuminate\Support\Facades\Queue::fake([DefaultExport::class]);

    livewire(ListAdmins::class)
        ->callTableBulkAction(
            ExportBulkAction::class,
            $admins,
            ['writer_type' => \Maatwebsite\Excel\Excel::XLSX]
        );

    Excel::assertQueued(
        config('filament-export.temporary_files.base_directory').'/admins-'.now()->toDateTimeString().'.xlsx',
        config('filament-export.temporary_files.disk'),
        function (DefaultExport $excelExport) use ($admins) {
            assertCount(count($admins), $excelExport->query()->get());

            return true;
        }
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
    Excel::fake();

    livewire(ListAdmins::class)
        ->callPageAction(
            ExportAction::class,
            ['writer_type' => \Maatwebsite\Excel\Excel::XLSX]
        );

    Excel::assertQueued(
        config('filament-export.temporary_files.base_directory').'/admins-'.now()->toDateTimeString().'.xlsx',
        config('filament-export.temporary_files.disk'),
        function (DefaultExport $excelExport) {
            assertCount(3, $excelExport->query()->get());

            return true;
        }
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

    Excel::fake();

    livewire(ListAdmins::class)
        ->callPageAction(
            ImportAction::class,
            ['file' => [$file->store('tmp')]]
        );

    Excel::matchByRegex();
    // TODO: add fluent test for import in package
    Excel::assertImported(
        '/\w{40}\.csv/', // sample: N3AeJTyAYpDzW9OrcHxU7zMboUxgT35cQXbemcmZ.csv
        config('filament-import.temporary_files.disk'),
        function (DefaultImport $import) {
            return true;
        }
    );
    // TODO: add activity log on import package
    //    assertActivityLogged(
    //        logName: 'admin',
    //        event: 'imported',
    //        description: 'Imported Admin',
    //        causedBy: Filament::auth()->user(),
    //    );
});
