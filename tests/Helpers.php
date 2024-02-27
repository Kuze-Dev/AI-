<?php

declare(strict_types=1);

use App\Features\CMS\CMSBase;
use App\Features\ECommerce\ECommerceBase;
use Database\Seeders\Tenant\Auth\PermissionSeeder;
use Database\Seeders\Tenant\Auth\RoleSeeder;
use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Admin\Models\Admin;
use Domain\Tenant\Database\Factories\TenantFactory;
use Domain\Tenant\Models\Tenant;
use Domain\Tenant\TenantSupport;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\ActivitylogServiceProvider;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\seed;

function loginAsSuperAdmin(?Admin $admin = null): Admin
{
    return loginAsAdmin($admin)->assignRole(config('domain.role.super_admin'));
}

function loginAsAdmin(?Admin $admin = null): Admin
{
    $admin ??= AdminFactory::new()
        ->createOne();

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
            'log_name' => $logName ?? config('activitylog.default_log_name'),
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
    config([
        'tenancy.database.suffix' => '_'.Str::random(7),
    ]);

    Filament::setCurrentPanel(Filament::getPanels()['tenant']);

    /** @var Tenant */
    $tenant = TenantFactory::new()->createOne(['name' => 'testing']);

    $domain = 'test.'.parse_url(config('app.url'), PHP_URL_HOST);

    $tenant->createDomain(['domain' => $domain]);

    URL::forceRootUrl(Request::getScheme().'://'.$domain);

    tenancy()->initialize($tenant);

    activateFeatures(
        collect($features ?? [])
            ->merge([
                CMSBase::class, ECommerceBase::class,
            ])
            ->toArray()
    );

    seed([
        PermissionSeeder::class,
        RoleSeeder::class,
    ]);

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
