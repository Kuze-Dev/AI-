<?php

declare(strict_types=1);

namespace Domain\Auth;

use Domain\Auth\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\ServiceProvider;
use PragmaRX\Google2FA\Google2FA;

class AuthServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/auth.php', 'domain.auth');

        $this->app->singleton(
            TwoFactorAuthenticationProviderContract::class,
            function ($app) {
                return new TwoFactorAuthenticationProvider(
                    $app->make(Google2FA::class),
                    $app->make(Repository::class)
                );
            }
        );
    }
}
