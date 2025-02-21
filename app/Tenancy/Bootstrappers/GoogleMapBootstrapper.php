<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class GoogleMapBootstrapper implements TenancyBootstrapper
{
    protected ?string $originalKey;

    protected ?string $originalKeysWebKey;

    protected ?string $originalKeysServerKey;

    public function __construct(protected Application $app)
    {
        $this->originalKey = $this->app->make('config')['filament-google-maps.key'];
        $this->originalKeysWebKey = $this->app->make('config')['filament-google-maps.keys.web_key'];
        $this->originalKeysServerKey = $this->app->make('config')['filament-google-maps.keys.server_key'];
    }

    public function bootstrap(Tenant $tenant): void
    {
        $apiKey = $tenant->google_map_api_key ?? $tenant->getInternal('google_map_api_key');

        $this->app->make('config')->set('filament-google-maps.key', $apiKey);
        $this->app->make('config')->set('filament-google-maps.keys.web_key', $apiKey);
        $this->app->make('config')->set('filament-google-maps.keys.server_key', $apiKey);

    }

    public function revert(): void
    {
        $this->app->make('config')->set('filament-google-maps.key', $this->originalKey);
        $this->app->make('config')->set('filament-google-maps.keys.web_key', $this->originalKeysWebKey);
        $this->app->make('config')->set('filament-google-maps.keys.server_key', $this->originalKeysServerKey);

    }
}
