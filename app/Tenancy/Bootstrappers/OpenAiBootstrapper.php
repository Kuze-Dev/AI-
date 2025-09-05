<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class OpenAiBootstrapper implements TenancyBootstrapper
{
    protected ?string $originalApiKey;
    protected ?string $originalModel;

    public function __construct(protected Application $app)
    {
        $this->originalApiKey = $this->app->make('config')['services.openai.api_key'] ?? null;
        $this->originalModel = $this->app->make('config')['services.openai.model'] ?? 'gpt-4o-mini';
    }

    public function bootstrap(Tenant $tenant): void
    {
        $apiKey = $tenant->openai_api_key ?? $tenant->getInternal('openai_api_key');
        $model  = $tenant->openai_model ?? $tenant->getInternal('openai_model') ?? 'gpt-4o-mini';

        if ($apiKey) {
            $this->app->make('config')->set('services.openai.api_key', $apiKey);
        }

        $this->app->make('config')->set('services.openai.model', $model);
    }

    public function revert(): void
    {
        $this->app->make('config')->set('services.openai.api_key', $this->originalApiKey);
        $this->app->make('config')->set('services.openai.model', $this->originalModel);
    }
}
