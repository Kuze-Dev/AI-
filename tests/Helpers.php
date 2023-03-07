<?php

declare(strict_types=1);

use Domain\Admin\Database\Factories\AdminFactory;
use Domain\Admin\Models\Admin;
use Domain\Tenant\Database\Factories\TenantFactory;
use Domain\Tenant\Models\Tenant;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\actingAs;

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

function testInTenantContext(): Tenant
{
    /** @var Tenant */
    $tenant = TenantFactory::new()->createOne(['name' => 'testing']);

    $domain = 'test.'.parse_url(config('app.url'), PHP_URL_HOST);

    $tenant->createDomain(['domain' => $domain]);

    URL::forceRootUrl(Request::getScheme().'://'.$domain);

    tenancy()->initialize($tenant);

    return $tenant;
}
