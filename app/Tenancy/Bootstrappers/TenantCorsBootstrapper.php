<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class TenantCorsBootstrapper implements TenancyBootstrapper
{
    protected ?array $originalAllowedOrigins;

    public function __construct(protected Application $app)
    {
        $this->originalAllowedOrigins = $this->app->make('config')['cors.allowed_origins'];

    }

    public function bootstrap(Tenant $tenant): void
    {
        $allowed_origins = $tenant->getInternal('cors_allowed_origins');

        if (is_array($allowed_origins)) {

            $origins = array_values($allowed_origins);

            if (count($origins) !== 0) {

                $this->app->make('config')->set('cors.allowed_origins', $allowed_origins);
            }
        }

    }

    public function revert(): void
    {
        $this->app->make('config')->set('filament-google-maps.key', $this->originalAllowedOrigins);

    }
}
