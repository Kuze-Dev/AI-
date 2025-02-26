<?php

declare(strict_types=1);

use App\Features\CMS\CMSBase;
use App\Features\ECommerce\ECommerceBase;
use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Admin\Models\Admin;
use Domain\Role\Database\Factories\RoleFactory;
use Domain\Tenant\Models\Tenant;
use Domain\Tenant\TenantSupport;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\ActivitylogServiceProvider;

use Spatie\Permission\Contracts\Role;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

function loginAsSuperAdmin(?Admin $admin = null): Admin
{
    return loginAsAdmin($admin);//->assignRole(config('domain.role.super_admin'));
}

function loginAsAdmin(?Admin $admin = null): Admin
{
    $admin ??= (
        Admin::where(['email' => 'admin@admin.com'])->first()
        ?? AdminFactory::new() ->createOne(['email' => 'admin@admin.com',])
    );

    $role = app(Role::class)
        ->createOrFirst(['name' => config('domain.role.super_admin')]);

    $admin->syncRoles($role);

    return tap($admin, actingAs(...));
}

function loginAsUser(?Admin $user = null): Admin
{
    $user ??= AdminFactory::new()
        ->createOne();

    return tap($user, actingAs(...));
}

function assertActivityLogged(
    ?string $logName = null,
    ?string $event = null,
    ?string $description = null,
    ?array $properties = null,
    ?Model $causedBy = null,
    ?Model $subject = null,
): void {
    assertDatabaseHas(
        ActivitylogServiceProvider::determineActivityModel(),
        array_filter([
            // 'log_name' => $logName ?? config('activitylog.default_log_name'),
            'event' => $event,
            'description' => $description,
            'properties' => $properties ? json_encode($properties) : null,
            'causer_type' => $causedBy?->getMorphClass(),
            'causer_id' => $causedBy?->getKey(),
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
        ])
    );
}

function testInTenantContext(array|string|null $features = null): Tenant
{
    tenancy()->initialize($tenant = Tenant::first());

    URL::useOrigin('http://foo.hasp.test');

    Filament::setCurrentPanel(Filament::getPanels()['tenant']);

    /**
     * since Livewire doesn't need to keep track of the UUIDs in a test,
     * you can disable the UUID generation and replace them with numeric keys,
     * using the Repeater::fake() method at the start of your test:
     *
     * https://filamentphp.com/docs/3.x/forms/fields/repeater#customizing-the-repeater-item-actions
     */
    Repeater::fake();

    activateFeatures(
        collect($features ?? [])
            ->merge([
                CMSBase::class, ECommerceBase::class,
            ])
            ->toArray()
    );

    return $tenant;
}

// https://github.com/konnco/filament-import/blob/1.5.2/tests/Pest.php#L16
function csvFiles(callable $fakeRows, int $rowCount = 10): Illuminate\Http\Testing\File
{
    Storage::fake('uploads');

    $content = collect(''); // headings, not necessary for testing

    for ($i = 0; $i < $rowCount; $i++) {
        $content = $content->push(implode(',', value($fakeRows)));
    }

    return UploadedFile::fake()
        ->createWithContent(
            name: 'import-file.csv',
            content: $content->join("\n")
        );
}

function activateFeatures(string|array $features): void
{
    if (blank($features)) {
        return;
    }

    $tenant = TenantSupport::model();

    foreach (Arr::wrap($features) as $feature) {
        $tenant->features()->activate($feature);
    }

}
function deactivateFeatures(string|array $features): void
{
    if (blank($features)) {
        return;
    }

    $tenant = TenantSupport::model();

    foreach (Arr::wrap($features) as $feature) {
        $tenant->features()->deactivate($feature);
    }

}

/**
 * @template TObject as object
 *
 * @param  class-string<TObject>|TObject  $object
 * @return TObject|\Mockery\MockInterface
 */
function mock_expect(string|object $object, callable ...$methods): mixed
{
    /** @var TObject|\Mockery\MockInterface $mock */
    $mock = mock($object);

    foreach ($methods as $method => $expectation) {
        /* @phpstan-ignore-next-line */
        $m = $mock
            ->shouldReceive((string) $method)
            ->atLeast()
            ->once();

        $m->andReturnUsing($expectation);
    }

    return $mock;

//
//    return mock($object)
//        ->shouldReceive((string) $method)
//        ->atLeast()
//        ->once()
//        ->andReturnUsing($expectation);


}
