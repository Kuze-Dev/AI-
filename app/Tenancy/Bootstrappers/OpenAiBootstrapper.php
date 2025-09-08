<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Illuminate\Contracts\Foundation\Application;
use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;

class OpenAiBootstrapper implements TenancyBootstrapper
{
    protected ?string $originalApiKey;

    public function __construct(protected Application $app)
    {
        $this->originalApiKey = $this->app->make('config')['services.openai.api_key'] ?? null;
    }

    public function bootstrap(Tenant $tenant): void
    {
        $apiKey = $tenant->openai_api_key ?? $tenant->getInternal('openai_api_key');

        if ($apiKey) {
            $this->app->make('config')->set('services.openai.api_key', $apiKey);
        }

        // Always GPT-5
        $this->app->make('config')->set('services.openai.model', 'gpt-5');
    }

    public function revert(): void
    {
        $this->app->make('config')->set('services.openai.api_key', $this->originalApiKey);
        $this->app->make('config')->set('services.openai.model', 'gpt-5');
    }
}
