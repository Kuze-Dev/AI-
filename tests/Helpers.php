<?php

declare(strict_types=1);

use App\Features\CMS\CMSBase;
use App\Features\ECommerce\ECommerceBase;
use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Admin\Models\Admin;
use Domain\Tenant\Database\Factories\TenantFactory;
use Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\ActivitylogServiceProvider;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

function loginAsSuperAdmin(Admin $admin = null): Admin
{
    return loginAsAdmin($admin)->assignRole(config('domain.role.super_admin'));
}

function loginAsAdmin(Admin $admin = null): Admin
{
    $admin ??= AdminFactory::new()
        ->createOne();

    return tap($admin, actingAs(...));
}

function loginAsUser(Admin $user = null): Admin
{
    $user ??= AdminFactory::new()
        ->createOne();

    return tap($user, actingAs(...));
}

function assertActivityLogged(
    string|null $logName = null,
    string|null $event = null,
    string|null $description = null,
    array|null $properties = null,
    Model|null $causedBy = null,
    Model|null $subject = null,
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

function testInTenantContext(): Tenant
{
    /** @var Tenant */
    $tenant = TenantFactory::new()->createOne(['name' => 'testing']);

    $domain = 'test.' . parse_url(config('app.url'), PHP_URL_HOST);

    $tenant->createDomain(['domain' => $domain]);

    $tenant->features()->activate(CMSBase::class);
    $tenant->features()->activate(ECommerceBase::class);

    URL::forceRootUrl(Request::getScheme() . '://' . $domain);

    tenancy()->initialize($tenant);

    return $tenant;
}
