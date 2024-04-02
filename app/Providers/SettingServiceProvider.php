<?php

declare(strict_types=1);

namespace App\Providers;

use App\Settings\Support\SettingsCacheFactory as SettingsCacheFactoryApp;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelSettings\SettingsContainer;
use Spatie\LaravelSettings\Support\SettingsCacheFactory as SettingsCacheFactorySpatie;

class SettingServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->app->bind(
            SettingsCacheFactorySpatie::class,
            fn () => new SettingsCacheFactoryApp()
        );
        $settingsContainer = app(SettingsContainer::class);
        $settingsContainer->registerBindings();
    }
}
