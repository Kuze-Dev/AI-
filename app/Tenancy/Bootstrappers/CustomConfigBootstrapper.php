<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class CustomConfigBootstrapper implements TenancyBootstrapper
{
    protected bool $strict_api;

    public function __construct(protected Application $app)
    {
        $this->strict_api = $this->app->make('config')['custom.strict_api'];

    }

    public function bootstrap(Tenant $tenant): void
    {
        if (! is_null(
            $tenant->getInternal('strict_api')
        )
        ) {
            $this->app->make('config')->set('custom.strict_api', $tenant->getInternal('strict_api'));
        }

    }

    public function revert(): void
    {
        $this->app->make('config')->set('custom.strict_api', $this->strict_api);
    }
}
