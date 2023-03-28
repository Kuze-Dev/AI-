<?php

declare(strict_types=1);

namespace App\Settings\Support;

use Spatie\LaravelSettings\SettingsCache;

class SettingsCacheFactory extends \Spatie\LaravelSettings\Support\SettingsCacheFactory
{
    protected function initializeCaches(): void
    {
        // ignore parent method
    }

    public function build(?string $repository = null): SettingsCache
    {
        return new SettingsCache(
            (bool) config('settings.cache.enabled', false),
            config('settings.cache.store'),
            tenant()?->getTenantKey() ?? 'central',
            config('settings.cache.ttl'),
        );
    }
}
