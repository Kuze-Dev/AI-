<?php

declare(strict_types=1);

namespace Domain\Auth;

use Domain\Auth\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Domain\Auth\Events\PasswordResetSent;
use Domain\Auth\Listeners\LogPasswordReset;
use Domain\Auth\Listeners\LogPasswordResetSent;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use PragmaRX\Google2FA\Google2FA;

class AuthServiceProvider extends EventServiceProvider
{
    /** @var array<class-string, array<int, class-string>> */
    protected $listen = [
        PasswordReset::class => [
            LogPasswordReset::class,
        ],
        PasswordResetSent::class => [
            LogPasswordResetSent::class,
        ],
    ];

    #[\Override]
    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__.'/config/auth.php', 'domain.auth');

        $this->app->singleton(
            TwoFactorAuthenticationProviderContract::class,
            fn ($app) => new TwoFactorAuthenticationProvider(
                $app->make(Google2FA::class),
                $app->make(Repository::class)
            )
        );
    }
}
