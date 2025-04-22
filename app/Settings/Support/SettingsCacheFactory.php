<?php

declare(strict_types=1);

namespace App\Settings\Support;

use Spatie\LaravelSettings\SettingsCache;

class SettingsCacheFactory extends \Spatie\LaravelSettings\Support\SettingsCacheFactory
{
    public function __construct()
    {
        // to totally ignore initializeCaches()
    }

    #[\Override]
    public function all(): array
    {
        return [$this->build()];
    }

    #[\Override]
    public function build(?string $repository = null): SettingsCache
    {
        return new SettingsCache(
            config()->boolean('settings.cache.enabled', false),
            config('settings.cache.store'),
            tenant()?->getTenantKey() ?? 'central',
            config('settings.cache.ttl'),
        );
    }
}
