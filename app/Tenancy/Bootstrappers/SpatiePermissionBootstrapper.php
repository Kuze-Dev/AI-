<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Spatie\Permission\PermissionRegistrar;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class SpatiePermissionBootstrapper implements TenancyBootstrapper
{
    public function __construct(
        protected PermissionRegistrar $registrar,
        protected Application $app
    ) {}

    public function bootstrap(Tenant $tenant): void
    {
        $this->registrar->cacheKey = 'spatie.permission.cache.tenant.'.$tenant->getTenantKey();

        $this->app->make('config')->set('permission.cache.key', 'tenant.'.$tenant->getTenantKey());

    }

    public function revert(): void
    {
        $this->registrar->cacheKey = 'spatie.permission.cache';

        $this->app->make('config')->set('permission.cache.key', 'spatie.permission.cache');
    }
}
